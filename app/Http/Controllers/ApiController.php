<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('apiauth');
    }

    public function checkLogin(Request $request)
    {
        $employeeEmpId = $request->input("employeeEmpId");
        $employeePassword = $request->input("employeePassword");

        $credentials = ['EmpId' => $employeeEmpId, 'password' => $employeePassword];
        $empDetails = DB::table("mas_employee")->where("EmpId", $employeeEmpId)->get(["Id", "Name", "Status"]);
        if (Auth::once($credentials)) {
            //EMPLOYEE USERNAME AND PASSWORD CORRECT
            $status = (int) $empDetails[0]->Status;
            $id = $empDetails[0]->Id;
            $name = $empDetails[0]->Name;
            return Response::json(['success' => true, 'id' => $id, 'code' => 1, 'message' => 'Authentication Successful', 'status' => $status, 'name' => $name], 200);
        }
        if (count($empDetails) > 0) {
            //EMPLOYEE PASSWORD WRONG
            return Response::json(['success' => false, 'code' => 2, 'message' => "Wrong Password"], 500);
        }

        //EMPLOYEE DOESN'T EXIST
        return Response::json(['success' => false, 'code' => 3, 'message' => 'Employee does not exist'], 500);
    }
    
}