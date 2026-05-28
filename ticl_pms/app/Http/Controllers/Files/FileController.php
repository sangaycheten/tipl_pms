<?php


namespace App\Http\Controllers\Files;

use App\Document;
use App\DocumentCategory;
use App\DocumentFileDepartment;
use App\Http\Controllers\Controller;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Crypt;
use Illuminate\Support\Facades\Input;

class FileController extends Controller
{
    public function getDisplay(Request $request)
    {
        $userDepartmentId = Auth::user()->DepartmentId;
        $departmentId = $request->DepartmentId;
        $categoryId = $request->CategoryId;
        $fileName = $request->FileName;

        $condition = "1=1";
        $params = [];

        if ((bool) $departmentId) {
            $condition .= " and T2.DepartmentId = ?";
            array_push($params, $departmentId);
        } else {
            $departmentId = "zz";
        }
        if ((bool) $categoryId) {
            $condition .= " and T1.CategoryId = ?";
            array_push($params, $categoryId);
        }
        if ((bool) $fileName) {
            $condition .= " and T1.Name like ?";
            array_push($params, "%$fileName%");
        }

        $perPage = 200;
        $files = DB::table('doc_file as T1')
            ->join('doc_category as T2', 'T2.Id', '=', 'T1.CategoryId')
            ->join('mas_department as T3', 'T3.Id', '=', 'T2.DepartmentId')
            ->select(['T1.Name', 'T1.Id', DB::raw('coalesce(T1.updated_at,T1.created_at) as updated_at'), 'T1.FilePath', 'T3.Name as Department', 'T2.Name as Category', DB::raw("(select GROUP_CONCAT(B.DepartmentId SEPARATOR ',') from doc_filedepartment B where B.FileId = T1.Id) as Depts")])
            ->orderBy('T3.Name')
            ->orderBy('T2.Name')
            ->orderBy('T1.Name')
            ->whereRaw("$condition", $params)
            ->paginate($perPage);

        $categories = DB::select("SELECT T1.Id, T1.Name, T1.DepartmentId from doc_category T1 join doc_file T2 on T2.CategoryId = T1.Id where T1.Status = 1 and T1.DepartmentId = ? group by T1.Id", [$departmentId]);
        $departments = DB::select("SELECT T1.Id, T1.Name,T1.ShortName from mas_department T1 join doc_category T2 on T2.DepartmentId = T1.Id join doc_file T3 on T3.CategoryId = T2.Id where T1.Status = 1 group by T1.Id");

        return view('files.filedisplay', ['perPage' => $perPage, 'files' => $files, 'categories' => $categories, 'departments' => $departments]);
    }

    public function getCategoryIndex(Request $request)
    {
        $departmentId = $request->DepartmentId;
        $name = $request->Name;
        $parameters = [];
        $condition = "1=1";
        if ((bool) $departmentId) {
            $condition .= " and T1.DepartmentId = ?";
            array_push($parameters, $departmentId);
        }
        if ((bool) $name) {
            $condition .= " and T1.Name like ?";
            array_push($parameters, "%$name%");
        }

        $data['perPage'] = 15;
        $data['departments'] = DB::select("SELECT Id, ShortName from mas_department order by ShortName");
        $categories = DB::table("doc_category as T1")->join('mas_department as T2', 'T1.DepartmentId', '=', 'T2.Id')->orderBy('T2.Name')->orderBy('T1.Name')->whereRaw("$condition", $parameters)->select(['T1.Id', 'T2.Name as Department', 'T1.Name', DB::raw("case when T1.Status = 1 then 'Active' else 'In-active' end as Status")])->paginate($data['perPage']);
        $categories->setPath("filecategoryindex");
        $data['categories'] = $categories;
        return view('files.categoryindex', $data);
    }

    public function getCategoryForm($id = null)
    {
        $data['update'] = false;
        if ((bool) $id) {
            $data['update'] = true;
            $data['category'] = DB::select("SELECT Id, DepartmentId,Status, Name from doc_category where Id = ?", [$id]);
            if (empty($data['category'])) {
                abort(404);
            }
        } else {
            $data['category'] = [new DocumentCategory];
        }

        $data['departments'] = DB::select("SELECT Id, Name from mas_department order by ShortName");
        return view('files.categoryform', $data);
    }

    public function saveCategory(Request $request)
    {
        $inputs = $request->input();
        $rules = [
            'DepartmentId' => "required",
            "Name" => "required"
        ];
        $messages = [
            'DepartmentId.required' => "The Department field is required",
            'Name.required' => "The Name field is required"
        ];
        $validation = $this->validate($request, $rules, $messages);
        $action = 'saved';
        DB::beginTransaction();
        try {
            if ((bool) $request->Id) {
                $action = "updated";
                $updateObject = DocumentCategory::find($request->Id);
                $inputs['EditedBy'] = Auth::user()->Id;
                $inputs['updated_at'] = date('Y-m-d H:i:s');
                $updateObject->fill($inputs);
                $changes = $updateObject->getDirty();
                if (!(count($changes) == 2 && array_key_exists('updated_at', $changes) && array_key_exists('EditedBy', $changes)) && !(count($changes) == 1 && array_key_exists('updated_at', $changes))) {
                    unset($changes['EditedBy']);
                    unset($changes['updated_at']);
                    $changes['Id'] = $inputs['Id'];
                    $recordJson = json_encode([$changes]);
                    DB::insert("INSERT into sys_databasechangehistory (Id, TableName, EmployeeId, Deleted, Changes) VALUES (UUID(),?,?,?,?)", ['doc_category', Auth::user()->Id, 0, $recordJson]);
                }
                $updateObject->update();
            } else {
                $saveAudit = true;
                $inputs['Id'] = UUID();
                $inputs['CreatedBy'] = Auth::user()->Id;
                DocumentCategory::create($inputs);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->saveError($e, false);
            return back()->withInput()->with('errormessage', "Something went wrong!");
        }

        DB::commit();
        if (isset($saveAudit) && $saveAudit) {
            $this->saveAuditTrail('doc_category', $inputs['Id']);
        }

        return redirect('filecategoryindex')->with('successmessage', "Record has been $action");
    }

    public function getDeleteCategory($id)
    {
        try {
            $this->saveAuditTrail('doc_category', $id, 1);
            DocumentCategory::where('Id', $id)->delete();
        } catch (\Exception $e) {
            $this->saveError($e, false);
            return back()->with('errormessage', "Document Category could not be deleted because there are Documents or other records related to this Document Category.");
        }

        return back()->with('successmessage', 'Record has been deleted');
    }

    public function getFileIndex(Request $request)
    {
        $departmentId = $request->DepartmentId;
        $categoryId = $request->CategoryId;
        $fileName = $request->FileName;

        $condition = "1=1";
        $params = [];
        if ((bool) $departmentId) {
            $condition .= " and T2.DepartmentId = ?";
            array_push($params, $departmentId);
        }
        if ((bool) $categoryId) {
            $condition .= " and T1.CategoryId = ?";
            array_push($params, $categoryId);
        }
        if ((bool) $fileName) {
            $condition .= " and T1.Name like ?";
            array_push($params, "%$fileName%");
        }

        $data['perPage'] = 15;
        $files = DB::table('doc_file as T1')
            ->join('doc_category as T2', 'T2.Id', '=', 'T1.CategoryId')
            ->join('mas_department as T3', 'T3.Id', '=', 'T2.DepartmentId')
            ->select(['T1.Name', 'T1.Id', 'T1.FilePath', 'T3.Name as Department', DB::raw("case when (select count(Id) from mas_department where Id not in (select B.DepartmentId from doc_filedepartment B where B.FileId = T1.Id and B.DepartmentId <> 99)) = 0 then '1' else '0' end as OrgWide"), DB::raw("case when (select B.DepartmentId from doc_filedepartment B where B.FileId = T1.Id and B.DepartmentId = 99) = 99 then '1' else '0' end as VisibleToManagement"), DB::raw("(select GROUP_CONCAT(coalesce(A.ShortName,case when B.DepartmentId = 99 then 'Management Team' else '' end) SEPARATOR ', ') from doc_filedepartment B left join mas_department A on A.Id = B.DepartmentId where B.FileId = T1.Id) as Visibility"), 'T2.Name as Category'])
            ->orderBy('T3.Name')
            ->orderBy('T2.Name')
            ->orderBy('T1.Name')
            ->whereRaw("$condition", $params)
            ->paginate($data['perPage']);
        $files->setPath("fileindex");
        $data['files'] = $files;
        $data['categories'] = DB::select("SELECT Id, Name, DepartmentId from doc_category where Status = 1");
        $data['departments'] = DB::select("SELECT Id, Name,ShortName from mas_department where Status = 1");

        return view('files.fileindex', $data);
    }

    public function getFileForm($id = null)
    {
        $data['update'] = false;
        if ((bool) $id) {
            $data['update'] = true;
            $data['file'] = DB::select("SELECT T1.Id, T1.CategoryId, T2.DepartmentId, T1.Name, T1.FilePath, T1.VisibilityLevel, T1.Status from doc_file T1 join doc_category T2 on T2.id = T1.CategoryId where T1.Id = ?", [$id]);
            $data['filedepartments'] = DB::table('doc_filedepartment as T1')->where('T1.FileId', $id)->pluck("DepartmentId");
            if (empty($data['file'])) {
                abort(404);
            }
        } else {
            $data['file'] = [new Document];
            $data['filedepartments'] = [];
        }

        $data['categories'] = DB::select("SELECT Id, Name, DepartmentId from doc_category where Status = 1");
        $data['departments'] = DB::select("SELECT Id, Name,ShortName from mas_department where Status = 1");
        return view('files.fileform', $data);
    }

    public function saveFile(Request $request)
    {
        $action = 'updated';
        $inputs = $request->except("VisibilityLevel");

        if ((bool) $inputs['Id']) {
            $rules = [
                'CategoryId' => "required",
                "Name" => "required"
            ];
            $messages = [
                'CategoryId.required' => "The Category field is required",
                'Name.required' => "The Name field is required"
            ];
        } else {
            $rules = [
                'CategoryId' => "required",
                'FileUpload' => "required|file|mimes:pdf",
                "Name" => "required"
            ];
            $messages = [
                'CategoryId.required' => "The Category field is required",
                'FileUpload.required' => "The File field is required",
                'FileUpload.file' => "The File must be a valid file",
                //LARAVEL FILE VALIDATION
                'FileUpload.mimes' => "Wrong file format. Please upload pdf document only",
                //LARAVEL FILE TYPE VALIDATION
                'Name.required' => "The Name field is required"
            ];
        }

        $validation = $this->validate($request, $rules, $messages);

        unset($inputs['DepartmentId']);
        if ($request->hasFile('FileUpload')) {
            if ((bool) $inputs['Id']) {
                $oldFile = DB::table('doc_file')->where('Id', $inputs['Id'])->value('FilePath');
                File::delete($oldFile);
            }

            $directory = 'documents';
            $file = $request->file('FileUpload');
            $extension = $file->getClientOriginalExtension();
            if (!$this->in_arrayi($extension, ['pdf'])) {
                return back()->with('errormessage', 'Wrong file format. Please upload pdf document only');
            }

            $fileName = $inputs['Name'] . '_' . $request->DepartmentId . '_' . randomString() . randomString() . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $fileName);
            $inputs['FilePath'] = $directory . '/' . $fileName;
        }

        DB::beginTransaction();
        try {
            if ((bool) $inputs['Id']) {
                $updateObject = Document::find($inputs['Id']);
                $inputs['EditedBy'] = Auth::user()->Id;
                $inputs['updated_at'] = date('Y-m-d H:i:s');
                $updateObject->fill($inputs);
                $changes = $updateObject->getDirty();
                if (!(count($changes) == 2 && array_key_exists('updated_at', $changes) && array_key_exists('EditedBy', $changes)) && !(count($changes) == 1 && array_key_exists('updated_at', $changes))) {
                    unset($changes['EditedBy']);
                    unset($changes['updated_at']);
                    $changes['Id'] = $inputs['Id'];
                    $recordJson = json_encode([$changes]);
                    DB::insert("INSERT into sys_databasechangehistory (Id, TableName, EmployeeId, Deleted, Changes) VALUES (UUID(),?,?,?,?)", ['doc_file', Auth::user()->Id, 0, $recordJson]);
                }

                DocumentFileDepartment::where('FileId', $inputs['Id'])->delete();

                $updateObject->update();
                if ($request->has("VisibilityLevel")) {
                    foreach ($request->input('VisibilityLevel') as $departmentId):
                        if ($departmentId != 100) {
                            $mapInputs['Id'] = UUID();
                            $mapInputs['DepartmentId'] = $departmentId;
                            $mapInputs['FileId'] = $inputs['Id'];
                            $mapInputs['CreatedBy'] = Auth::user()->Id;
                            DocumentFileDepartment::create($mapInputs);
                        }
                    endforeach;
                } else {
                    DB::insert("INSERT INTO doc_filedepartment (Id,FileId,DepartmentId,CreatedBy) SELECT UUID(),?,Id,? from mas_department where coalesce(Status,0)=1", [$inputs['Id'], Auth::user()->Id]);
                }
            } else {
                $action = 'saved';
                $saveAudit = true;
                $inputs['CreatedBy'] = Auth::user()->Id;
                $inputs['Id'] = UUID();

                Document::create($inputs);
                if ($request->has("VisibilityLevel")) {
                    foreach ($request->input('VisibilityLevel') as $departmentId):
                        if ($departmentId != 100) {
                            $mapInputs['Id'] = UUID();
                            $mapInputs['DepartmentId'] = $departmentId;
                            $mapInputs['FileId'] = $inputs['Id'];
                            $mapInputs['CreatedBy'] = Auth::user()->Id;
                            DocumentFileDepartment::create($mapInputs);
                        }
                    endforeach;
                } else {
                    DB::insert("INSERT INTO doc_filedepartment (Id,FileId,DepartmentId,CreatedBy) SELECT UUID(),?,Id,? from mas_department where coalesce(Status,0)=1", [$inputs['Id'], Auth::user()->Id]);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->saveError($e, false);
            return back()->withInput()->with('errormessage', "Something went wrong!");
        }

        DB::commit();
        if (isset($saveAudit) && $saveAudit) {
            $this->saveAuditTrail('doc_file', $inputs['Id']);
        }

        $redirectPage = $request->has('RedirectPage') ? $request->get('RedirectPage') : 1;
        return redirect('fileindex?redirectpage=' . $redirectPage)->with('successmessage', "Record has been $action");
    }

    public function getDeleteFile($id)
    {
        try {
            $this->saveAuditTrail('doc_file', $id, 1);
            $filePath = DB::table('doc_file')->where('Id', $id)->value('FilePath');
            Document::where('Id', $id)->delete();
        } catch (\Exception $e) {
            $this->saveError($e, false);
            return back()->with('errormessage', "Document could not be deleted.");
        }

        File::delete($filePath);
        return back()->with('successmessage', 'Record has been deleted');
    }

    public function downloadFile()
    {
        $file = Input::get('file');
        return response()->download($file);
    }

    public function getRender(Request $request)
    {
        $data['file'] = Crypt::decrypt($request->z);
        $data['name'] = Crypt::decrypt($request->w);
        //if(Auth::user()->EmpId == "714"): dd($data); endif;
        return view('files.filerender', $data);
    }

    public function fetchCategoriesOnDept(Request $request)
    {
        $deptId = $request->deptId;
        $categories = DB::select("SELECT distinct T1.Id, T1.Name from doc_category T1 join doc_file T2 on T2.CategoryId = T1.Id join doc_filedepartment T3 on T3.FileId = T2.Id where T1.DepartmentId = ? and T3.DepartmentId = ?", [$deptId, Auth::user()->DepartmentId]);
        return response()->json($categories);
    }
    
}
