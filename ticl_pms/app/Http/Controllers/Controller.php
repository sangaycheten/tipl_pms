<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Support\Facades\DB; //DB (query builder)
use Auth;
use Mail;
use App\ErrorLog as ErrorLog;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        $date = date_create("2020-01-10");
    }

    public function testPDF()
    {
        $imagick = new \Imagick();

        $imagick->readImage(public_path('fileuploads/Documentation/Online_PMS_Documentation.pdf'));

        $saveImagePath = public_path('converted.jpg');
        $imagick->writeImages($saveImagePath, true);

        return response()->file($saveImagePath);
    }

    public function getGradeStepPayScale($gradeStepId)
    {
        $gradeDetails = DB::select("SELECT a.Id, a.Name AS GradeName, a.PayScale FROM mas_gradestep a WHERE a.Id = ? ", [$gradeStepId]);
        if ((bool) $gradeDetails) {
            return $gradeDetails;
        }
    }

    public function getGoalandTasksTargetDetails() 
    {
        $goalsandTasksTargets = DB::select("SELECT T1.Id, T3.TaskId, T7.Email, T7.Name AS EmployeeName, T1.GoalDescription, T3.TaskDescription, T4.Name AS GoalType, T1.SysPmsNumberId, T3.EmployeeId, T5.ShortName AS Department, T6.Name AS Section, T3.Target, T8.StartDate, T8.EndDate 
            FROM pms_goals_target as T1 JOIN pms_goals_targetdetails T2 ON T2.GoalId = T1.Id JOIN pms_task_targetdetails T3 ON T3.GoalId = T2.GoalId JOIN mas_goal_type T4 ON T4.Id = T1.GoalTypeId JOIN mas_department T5 ON T5.Id = T2.DepartmentId 
            JOIN mas_section T6 ON T6.DepartmentId = T5.Id JOIN mas_employee T7 ON T7.Id = T3.EmployeeId JOIN mas_goal_targets T8 ON T8.Name = T3.Target WHERE T3.StatusId = 1 GROUP BY T3.TaskId ");
        
        if ((bool) $goalsandTasksTargets) {
            return $goalsandTasksTargets;
        }
    }

    public function getLevel1AppraiserEmployees()
    {
        $level1employees = DB::select("SELECT T1.Id, T1.EmpId, T1.Name as EmployeeName, T2.Name as Designation, T3.ShortName as Department, T1.DepartmentId FROM mas_employee AS T1 JOIN mas_designation as T2 ON T2.Id = T1.DesignationId
            JOIN mas_department as T3 ON T3.Id = T1.DepartmentId JOIN mas_hierarchy as T4 ON T4.ReportingLevel1EmployeeId = T1.Id WHERE T1.Status = 1 GROUP BY T1.EmpId ");
        if ((bool) $level1employees) {
            return $level1employees;
        }
    }

    public function getEmployeeDetails($id)
    {
        $employeedetail = DB::select("SELECT e.Id, e.EmpId, e.Name AS EmployeeName, de.ShortName AS Department, ds.Name AS Designation FROM mas_employee e JOIN mas_department de ON de.Id = e.DepartmentId JOIN mas_designation ds ON ds.Id = e.DesignationId WHERE e.Id = ? ", [$id]);
        if ((bool) $employeedetail) {
            return $employeedetail;
        }
    }

    public function fetchActiveDepartments()
    {
        $departments = DB::select("SELECT Id, Name, ShortName from mas_department where Status = 1 order by Name");
        return $departments;
    }

    public function fetchGoalsType()
    {
        $goalTypes = DB::select("SELECT Id, Name from mas_goal_type order by NAME");
        return $goalTypes;
    }

    public function fetchGoalsAchievementsNoAction()
    {
        $goalsAchievements = DB::select("SELECT Id, ShortName, Name from mas_goal_achievement WHERE Id = 4 order by NAME");
        return $goalsAchievements;
    }

    public function fetchGoalsStatusNoAction()
    {
        $goalStatus = DB::select("SELECT Id, Name from mas_goal_status WHERE Id = 4 order by NAME");
        return $goalStatus;
    }

    public function fetchGoalsAchievements()
    {
        $goalsAchievements = DB::select("SELECT Id, ShortName, Name from mas_goal_achievement WHERE Id != 4 order by NAME");
        return $goalsAchievements;
    }

    public function fetchGoalsStatus()
    {
        $goalStatus = DB::select("SELECT Id, Name from mas_goal_status WHERE Id != 4 order by NAME");
        return $goalStatus;
    }

    public function fetchGoalTargetDetails()
    {
        $goalTargetDetails = DB::select("SELECT Id, Name, StartDate, EndDate from mas_goal_targets order by NAME");
        return $goalTargetDetails;
    }

    public function fetchTargetDetailsForAlertNotifications()
    {
        $targetDetailsForNotification = DB::select("SELECT Id, Name, StartDate, EndDate from mas_goal_targets WHERE Name != 'Custom Targets' order by NAME");
        return $targetDetailsForNotification;
    }

    public function getGoalTargetStatus()
    {
        $goalTargetStatus = DB::select("SELECT Id, Name from pms_goals_targets_status where Id NOT IN (2,3) order by Name");
        return $goalTargetStatus;
    }

    public function fetchSubOrdinateUsingLevel1($userId) {
        $subOrdinates = DB::select("SELECT T2.Id, T2.EmpId, T2.Name AS EmployeeName, T3.Name AS Department, T4.Name AS Section, T5.Name AS Designation, T1.ReportingLevel1EmployeeId, T6.Name AS Position 
            FROM mas_hierarchy T1 JOIN mas_employee T2 ON T2.Id = T1.EmployeeId JOIN mas_department T3 ON T3.Id = T2.DepartmentId JOIN mas_section T4 ON T4.DepartmentId = T3.Id 
            JOIN mas_designation T5 ON T5.Id = T2.DesignationId JOIN mas_gradestep T6 ON T6.Id = T2.GradeStepId
            WHERE T2.Status = 1 AND T1.ReportingLevel1EmployeeId = ? GROUP BY T2.EmpId", [$userId]);
            
            if ((bool) $subOrdinates) {
                return $subOrdinates;
            }
    }

    public function getAllPmsRounds() {
        $allPmsRounds = DB::select("SELECT Id, StartDate, PMSNumber FROM sys_pmsnumber ORDER BY StartDate");
        if ((bool) $allPmsRounds) {
            return $allPmsRounds;
        }
    }

    public function getPmsRounds() {
        $currentyear = Carbon::now()->format('Y');
        $pmsRounds = DB::select("SELECT Id, StartDate, PMSNumber FROM sys_pmsnumber WHERE YEAR(StartDate) = ? AND PMSNumber != 0 ", [$currentyear]);
        if ((bool) $pmsRounds) {
            return $pmsRounds;
        }
    }

    public function getGoalTargetForTasks($departmentId) {
        $goalTargets = DB::select("SELECT T2.Id, T2.GoalId, T2.EmployeeId, T1.GoalDescription, T2.Weightage, Target FROM pms_goals_target T1 JOIN pms_goals_targetdetails T2 ON T1.Id = T2.GoalId WHERE DepartmentId = ? GROUP BY T1.Id", [$departmentId]);
        if ((bool) $goalTargets) {
            return $goalTargets;
        }
    }

    public function getTasksWeightage($goalId, $employeeId) {
        $tasksWeightage = DB::select("SELECT SUM(T3.SelfScore) TaskSelfScore FROM pms_goals_target T1 JOIN pms_goals_targetdetails as T2 ON T2.GoalId = T1.Id JOIN pms_task_targetdetails AS T3 ON T3.GoalId = T2.GoalId WHERE T2.StatusId = 1 AND T2.GoalId = ? AND T2.EmployeeId = ? ", [$goalId, $employeeId]);
        if ((bool) $tasksWeightage) {
            return $tasksWeightage;
        }
    }

    public function getDepartmentEmployees($deptId, $excludeSelf = false, $json = false)
    {
        $parameters = [$deptId];
        $condition = " and 1=1";
        if ($excludeSelf) {
            $condition .= " and T1.Id <> ?";
            array_push($parameters, Auth::user()->Id);
        }
        $employees = DB::select("SELECT T1.Id,T1.Name, T2.Name as Designation,T1.CIDNo, T1.EmpId from mas_employee T1 left join mas_designation T2 on T2.Id = T1.DesignationId where T1.DepartmentId = ? and T1.RoleId <> 1 and coalesce(T1.Status,0) = 1$condition order by T1.Name", $parameters);
        if ($json) {
            return response()->json($employees);
        }

        return $employees;
    }

    public function getAllEmployees()
    {
        $parameters = [];
        $employees = DB::select("SELECT T1.Id,T1.Name, T2.Name as Designation,T1.EmpId, A.ShortName as Department from mas_employee T1 join mas_department A on A.Id = T1.DepartmentId join mas_designation T2 on T2.Id = T1.DesignationId where coalesce(T1.Status,0) = 1 and T1.RoleId <> 1 order by T1.Name", $parameters);
        return $employees;
    }

    public function getSectionEmployees($sectionId, $excludeSelf = false, $json = false)
    {
        $parameters = [$sectionId];
        $condition = " and 1=1";
        if ($excludeSelf) {
            $condition .= " and T1.Id <> ?";
            array_push($parameters, Auth::user()->Id);
        }
        $employees = DB::select("SELECT T1.Id,T1.Name, T2.Name as Designation from mas_employee T1 left join mas_designation T2 on T2.Id = T1.DesignationId where T1.SectionId = ? and T1.RoleId <> 1 and coalesce(T1.Status,0) = 1$condition order by T1.Name", $parameters);
        if ($json) {
            return response()->json($employees);
        }

        return $employees;
    }

    public function getDepartmentEmployeesAndMD($deptId, $excludeSelf = false, $selfId = null)
    {
        $parameters = [$deptId];
        $condition = " and 1=1";
        if ($excludeSelf) {
            $condition .= " and T1.Id <> ?";
            array_push($parameters, $selfId);
        }
        array_push($parameters, CONST_POSITION_MD);
        $employees = DB::select("SELECT T1.Id,T1.Name, O.Name as Designation from mas_employee T1 join mas_designation O on O.Id = T1.DesignationId where coalesce(T1.Status,0) = 1 and T1.DepartmentId = ? $condition union select T1.Id,T1.Name, O.Name as Designation from mas_employee T1 join mas_designation O on O.Id = T1.DesignationId left join mas_designation T2 on T2.Id = T1.DesignationId where T1.PositionId = ? order by Name", $parameters);
        return $employees;
    }

    public function testMail()
    {
        $mailBody = "<p>TEST MAIL!</p>";
        $this->sendMail('sangay.wangdi.moktan@gmail.com', $mailBody, "TEST_" . date('Y-m-d H:i:s'));
    }

    public function sendMail($recipientAddress, $mailBody, $subject, $ccAddresses = null, $bccAddresses = null, $attachment = null)
    {
        $env = (config('app.env'));
        Mail::send('emails.email',['mailBody'=>$mailBody],function($mail) use ($recipientAddress,$ccAddresses,$subject,$bccAddresses,$env,$attachment) {
        // Mail::queue('emails.email', ['mailBody' => $mailBody], function ($mail) use ($recipientAddress, $ccAddresses, $subject, $bccAddresses, $env, $attachment) {
            $mail->subject($subject);
            if ($env == 'local') {
                // $mail->to('web.mis@tashicell.com');
                $mail->to('sw_engineer4.sdu@tashicell.com');
            } else {
                $mail->to($recipientAddress);
            }

            if ((bool) $ccAddresses):
                foreach ($ccAddresses as $ccAddress):
                    if ($env == 'local') {
                        // $mail->cc('web.mis@tashicell.com');
                        $mail->cc('sw_engineer4.sdu@tashicell.com');
                    } else {
                        $mail->cc($ccAddress);
                    }
                endforeach;
            endif;

            if ((bool) $bccAddresses):
                foreach ($bccAddresses as $bccAddress):
                    if ($env == 'local') {
                        // $mail->bcc('web.mis@tashicell.com');
                        $mail->bcc('sw_engineer4.sdu@tashicell.com');
                    } else {
                        $mail->bcc($bccAddress);
                    }
                endforeach;
            endif;

            if ((bool) $attachment):
                $mail->attach($attachment);
            endif;
            $mail->from('ticl-alerts@tashicell.com', 'Online PMS');
        });
    }

    // public function sendMailAlternate($recipientAddress, $mailBody, $subject, $ccAddresses = null, $bccAddresses = null)
    // {
    //     $env = (config('app.env'));
    //     Mail::queue('emails.emailalternate', ['mailBody' => $mailBody], function ($mail) use ($recipientAddress, $ccAddresses, $subject, $bccAddresses, $env) {
    //         $mail->subject($subject);
    //         if ($env == 'local') {
    //             $mail->to('web.mis@tashicell.com');
    //         } else {
    //             $mail->to($recipientAddress);
    //         }
    //         if ((bool) $ccAddresses):
    //             foreach ($ccAddresses as $ccAddress):
    //                 if ($env == 'local') {
    //                     $mail->cc('web.mis@tashicell.com');
    //                 } else {
    //                     $mail->cc($ccAddress);
    //                 }
    //             endforeach;
    //         endif;
    //         if ((bool) $bccAddresses):
    //             foreach ($bccAddresses as $bccAddress):
    //                 if ($env == 'local') {
    //                     $mail->bcc('web.mis@tashicell.com');
    //                 } else {
    //                     $mail->bcc($bccAddress);
    //                 }
    //             endforeach;
    //         endif;
    //         $mail->from('info@tashicell.com', 'TashiCell');
    //     });
    // }
    
    public function sendMailAlternate($recipientAddress, $mailBody, $subject, $ccAddresses = null, $bccAddresses = null)
    {
        $env = (config('app.env'));
        // Mail::queue
        Mail::send('emails.emailalternate', ['mailBody' => $mailBody], function ($mail) use ($recipientAddress, $ccAddresses, $subject, $bccAddresses, $env) {
            $mail->subject($subject);
            if ($env == 'local') {
                $mail->to('sw_engineer4.sdu@tashicell.com');
            } else {
                $mail->to($recipientAddress);
            }

            if ((bool) $ccAddresses):
                foreach ($ccAddresses as $ccAddress):
                    if ($env == 'local') {
                        $mail->cc('sw_engineer4.sdu@tashicell.com');
                    } else {
                        $mail->cc($ccAddress);
                    }
                endforeach;
            endif;

            if ((bool) $bccAddresses):
                foreach ($bccAddresses as $bccAddress):
                    if ($env == 'local') {
                        $mail->bcc('sw_engineer4.sdu@tashicell.com');
                    } else {
                        $mail->bcc($bccAddress);
                    }
                endforeach;
            endif;

            $mail->from('info@tashicell.com', 'TashiCell');
        });
    }

    public function testSMS()
    {
        $this->sendSMS('97577883026', 'SMS Test');
    }

    // function sendSMS($mobile, $message)
    // {
    //     $env = (config('app.env'));

    //     if ($env == 'local') {
    //         $mobile = "97577883026";
    //     } else {
    //         if (strpos($mobile, '975') !== 0) {
    //             // $mobile = "975$mobile";
    //             $mobile = "97577883026";
    //         }
    //     }
        
    //     $message = urlencode($message);
    //     $post_fields = '';
                
    //     $postData = array(
    //         'UserName' => CONST_USER,
    //         'PassWord' => CONST_PASS,
    //         'UserData' => $message,
    //         'Concatenated' => '0',
    //         'Mode' => '0',
    //         'SenderId' => 'OnlinePMS',
    //         'Deferred' => 'false',
    //         'Number' => $mobile,
    //         'Dsr' => 'false',
    //         'Flash' => '0',
    //         'Date' => '0',
    //         'Hour' => '0',
    //         'Minute' => '0',
    //         'Second' => '0',
    //         'VP' => '720',
    //         'VlrData' => '0',
    //         'mbt' => '0'
    //     );

    //     foreach ($postData as $key => $value) {
    //         $post_fields .= $key . '=' . $value . '&';
    //     }
    //     $postFields = rtrim($post_fields, '&');

    //     $url = "http://10.76.177.100/cgi-bin/BMP_SendTextMsg?";
    //     $ch = curl_init();
    //     // curl_setopt_array($ch, array(
    //     //     CURLOPT_URL => $url,
    //     //     CURLOPT_RETURNTRANSFER => true,
    //     //     CURLOPT_POST => true,
    //     //     CURLOPT_POSTFIELDS => $postData
    //     // ));

    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     // curl_setopt($ch, CURLOPT_POST, count($postData));
    //     curl_setopt($ch, CURLOPT_POST, true);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


    //     // $output = curl_exec($ch);        
    //     // $return = 1;        
    //     // if (curl_errno($ch)) {
    //     //     $return = 0;
    //     // }
    //     // curl_close($ch);

    //     $exe  = curl_exec($ch);        
        
    //     return true;
    // }

    function sendSMS($mobile, $message)
    {
        $env = (config('app.env'));

        if ($env == 'local') {
            $mobile = "97577883026";
        } else {
            if (strpos($mobile, '975') !== 0) {
                $mobile = "975$mobile";                
            }
        }                    
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://10.76.177.100/cgi-bin/BMP_SendTextMsg?UserName=alert&PassWord=alert&UserData='.urlencode($message).'&Concatenated=0&Mode=0&SenderId=OnlinePMS&Deferred=false&Number='.urlencode($mobile).'&Dsr=false&Flash=0&Date=16%2F01%2F2024&Hour=0&Minute=0&Second=0&VP=720&VlrData=0&mbt=0&=null',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        // Check for cURL errors
        if ($response === false) {
            echo 'Curl error: ' . curl_error($curl);
        }

        curl_close($curl);
        return true;
    }

    // function sendSMSTashiCell($mobile, $message)
    // {
    //     $env = (config('app.env'));
    //     if ($env == 'local') {
    //         $mobile = "97577116699";
    //     } else {
    //         if (strpos($mobile, '975') !== 0) {
    //             $mobile = "975$mobile";
    //         }
    //     }
    //     $message = urlencode($message);
    //     $post_fields = '';

    //     $postData = array(
    //         'UserName' => CONST_USER,
    //         'PassWord' => CONST_PASS,
    //         'UserData' => $message,
    //         'Concatenated' => '0',
    //         'Mode' => '0',
    //         'SenderId' => 'OnlinePMS',
    //         'Deferred' => 'false',
    //         'Number' => $mobile,
    //         'Dsr' => 'false',
    //         'Flash' => '0',
    //         'Date' => '0',
    //         'Hour' => '0',
    //         'Minute' => '0',
    //         'Second' => '0',
    //         'VP' => '720',
    //         'VlrData' => '0',
    //         'mbt' => '0'
    //     );

    //     foreach ($postData as $key => $value) {
    //         $post_fields .= $key . '=' . $value . '&';
    //     }
    //     rtrim($post_fields, '&');

    //     $url = "http://10.76.177.100/cgi-bin/BMP_SendTextMsg?";
    //     $ch = curl_init();
    //     curl_setopt_array($ch, array(
    //         CURLOPT_URL => $url,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_POST => true,
    //         CURLOPT_POSTFIELDS => $postData
    //     )
    //     );

    //     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_POST, count($postData));
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

    //     $output = curl_exec($ch);
    //     $return = 1;
    //     if (curl_errno($ch)) {
    //         $return = 0;
    //     }
    //     curl_close($ch);
    //     return $return;
    // }

    function sendSMSTashiCell($mobile, $message)
    {
        $env = (config('app.env'));

        if ($env == 'local') {
            $mobile = "97577883026";
        } else {
            if (strpos($mobile, '975') !== 0) {
                $mobile = "975$mobile";                
            }
        }                  
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://10.76.177.100/cgi-bin/BMP_SendTextMsg?UserName=alert&PassWord=alert&UserData='.urlencode($message).'&Concatenated=0&Mode=0&SenderId=OnlinePMS&Deferred=false&Number='.urlencode($mobile).'&Dsr=false&Flash=0&Date=16%2F01%2F2024&Hour=0&Minute=0&Second=0&VP=720&VlrData=0&mbt=0&=null',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        // Check for cURL errors
        if ($response === false) {
            echo 'Curl error: ' . curl_error($curl);
        }

        curl_close($curl);
        return true;
    }

    public function updateDates()
    {
        $employees = DB::select("SELECT T1.EmpId, T1.DateOfAppointment, T1.DateOfBirth from employeeraw T1 where (T1.DateOfBirth like ('%JAN%')) or (T1.DateOfBirth like ('%FEB%')) or (T1.DateOfBirth like ('%MAR%')) or (T1.DateOfBirth like ('%APR%')) or (T1.DateOfBirth like ('%MAY%')) or (T1.DateOfBirth like ('%JUN%')) or (T1.DateOfBirth like ('%JUL%')) or (T1.DateOfBirth like ('%AUG%')) or (T1.DateOfBirth like ('%SEP%')) or (T1.DateOfBirth like ('%OCT%')) or (T1.DateOfBirth like ('%NOV%')) or (T1.DateOfBirth like ('%DEC%')) limit 30");
        if (count($employees) == 0) {
            dd('Complete!');
        }

        foreach ($employees as $employee):
            $updateArray = [];
            $empId = $employee->EmpId;
            $dateOfAppointment = $employee->DateOfAppointment;
            $dateOfBirth = $employee->DateOfBirth;
            $dateOfAppointment = $this->parseDate($dateOfAppointment, true);
            $dateOfBirth = $this->parseDate($dateOfBirth);

            if ($dateOfAppointment) {
                $updateArray['DateOfAppointment'] = $dateOfAppointment;
            }
            if ($dateOfBirth) {
                $updateArray['DateOfBirth'] = $dateOfBirth;
            }

            if (!empty($updateArray)) {
                DB::table('employeeraw')->where('EmpId', $empId)->update($updateArray);
            }
        endforeach;
    }

    function parseDate($date, $appointment = false)
    {
        $date = trim($date);
        $dateArray = explode("-", $date);
        $day = isset($dateArray[0]) ? $dateArray[0] : '01';
        $month = isset($dateArray[1]) ? $dateArray[1] : 'JAN';
        $year = isset($dateArray[2]) ? $dateArray[2] : '1980';

        $month = strtoupper($month);

        switch ($month):
            case "JAN":
                $month = "01";
                break;
            case "FEB":
                $month = "02";
                break;
            case "MAR":
                $month = "03";
                break;
            case "APR":
                $month = "04";
                break;
            case "MAY":
                $month = "05";
                break;
            case "JUN":
                $month = "06";
                break;
            case "JUL":
                $month = "07";
                break;
            case "AUG":
                $month = "08";
                break;
            case "SEP":
                $month = "09";
                break;
            case "OCT":
                $month = "10";
                break;
            case "NOV":
                $month = "11";
                break;
            case "DEC":
                $month = "12";
                break;
            default:
                return false;
                break;
        endswitch;

        $day = strlen($day) == 1 ? "0$day" : $day;
        if ($appointment) {
            $year = strlen($year) == 4 ? $year : "20$year";
        } else {
            $year = strlen($year) == 4 ? $year : "19$year";
        }

        return "$year-$month-$day";
    }

    public function pmsPeriodsForReports()
    {
        $pmsPeriods = DB::select("SELECT T1.Id, T1.PMSNumber, T1.StartDate from sys_pmsnumber T1 where T1.PMSNumber > 0 and T1.Status in (2,3) order by T1.PMSNumber");
        return $pmsPeriods;
    }

    public function fetchSections()
    {
        $sections = DB::select("SELECT T1.Id,T1.Name, T1.DepartmentId from mas_section T1 where coalesce(T1.Status,0) = 1 order by T1.DepartmentId, T1.Name");
        return $sections;
    }

    public function saveError(\Exception $e, $is404 = false)
    {
        $errorDesc = "Error Code: " . ($is404 ? '404' : $e->getCode());
        $errorDesc .= "<br/>Error Message: " . ($is404 ? 'Page not found' : $e->getMessage());
        $errorDesc .= "<br/>File: " . $e->getFile();
        $errorDesc .= "<br/>Line No.: " . $e->getLine();
        $errorDesc .= "<br/>URL: " . urldecode($_SERVER['REQUEST_URI']);
        $errorDesc .= "<br/>POST VARS: " . arrayToString($_POST);
        $errorDesc .= "<br/>GET VARS: " . arrayToString($_GET);
        $errorDesc .= "<br/>User Id: " . (isset(Auth::user()->Id) ? Auth::user()->Id : 'guest');
        $errorDesc .= "<br/>Trace: " . $e->getTraceAsString();

        $error['Id'] = UUID();
        $error['File'] = $e->getFile();
        $error['LineNo'] = $e->getLine();
        $error['Description'] = $errorDesc;
        $error['Message'] = ($is404 ? 'Page not found' : $e->getMessage());
        $error['URL'] = urldecode($_SERVER['REQUEST_URI']);
        $error['Code'] = ($is404 ? '404' : $e->getCode());
        $error['Date'] = date('Y-m-d H:i:s');

        if ($error['Code'] != '404') {
            $object = new Controller;
            $mailBody = $errorDesc;
            $object->sendMail('web.mis@tashicell.com', $mailBody, 'PMS Online: Error on ' . date('Y-m-d H:i:s'));
            $object->sendSMS(77116699, 'PMS Online: Error on ' . date('Y-m-d H:i:s'));
            $object->sendSMS(77106699, 'PMS Online: Error on ' . date('Y-m-d H:i:s'));
        }

        ErrorLog::create($error);
    }

    public function saveAuditTrail($tableName, $id, $deleted = 0)
    {
        $record = DB::table($tableName)->where('Id', $id)->get();
        $recordJson = json_encode($record);
        DB::insert("INSERT into sys_databasechangehistory (Id, TableName, EmployeeId, Deleted, Changes) VALUES (UUID(),?,?,?,?)", [$tableName, Auth::user()->Id, $deleted, $recordJson]);
    }

    public function fetchCurrentPMSAdjustmentDetails($id)
    {
        $applicationSubmissionDetails = DB::table('pms_submission')->where('Id', $id)->selectRaw("DATE_FORMAT(SubmissionTime,'%Y-%m-%d') as SubmittedAt")->pluck('SubmittedAt');
        $submissionTime = $applicationSubmissionDetails[0];
        $currentPMSQuery = DB::table('sys_pmsnumber')->where('StartDate', '<=', $submissionTime)->orderBy('StartDate', 'DESC')->get(['TargetRevenue', 'AchievedRevenue']);
        $targetRevenue = $currentPMSQuery[0]->TargetRevenue;
        $achievedRevenue = $currentPMSQuery[0]->AchievedRevenue;
        $adjustmentPercentage = DB::table('mas_pmssettings')->orderBy('created_at', 'DESC')->pluck('FinalAdjustmentPercent');
        $adjustmentPercentage = isset($adjustmentPercentage[0]) ? $adjustmentPercentage[0] : false;

        if (!(bool) $targetRevenue || !(bool) $achievedRevenue || !(bool) $adjustmentPercentage) {
            return false;
        } else {
            return ['Adjustment' => $adjustmentPercentage, 'ScoreToInject' => round(($achievedRevenue / $targetRevenue * $adjustmentPercentage), 2)];
        }
    }

    function in_arrayi($needle, $haystack)
    {
        return in_array(strtolower($needle), array_map('strtolower', $haystack));
    }

    public function officeSuiteDashboard()
    {
        $userId = Auth::id();
        return redirect("https://office-suite.tashicell.com/redirectdashboard?uid=12" . $userId . "88");
    }
    
}
