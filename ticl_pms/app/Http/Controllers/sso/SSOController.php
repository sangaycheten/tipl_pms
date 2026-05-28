<?php

namespace App\Http\Controllers\sso;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SSOController extends Controller
{
    public function getIndex(Request $request)
    {
        $token = $request->input("token");
        $parts = explode("|", $token);
        $a = $parts[0];
        $b = $parts[1];
        $c = $parts[2];

        $empId = (int) $a / 9;

        $empIdString = $empId;
        if (strlen($empIdString) === 1) {
            $empId = "00$empId";
        }
        if (strlen($empIdString) === 2) {
            $empId = "0$empId";
        }
        $empDetails = DB::table("mas_employee")->whereRaw("EmpId = ?", [$empId])->get(['Id', 'MobileNo', 'Email']);
        if (!count($empDetails)) {
            abort(404);
        }
        $mobileNo = $empDetails[0]->MobileNo;
        $payloadClient = (int) $empId . "@" . $mobileNo . "@" . date("Y_m_d");

        //CHECK 1
        $check1 = Hash::check($payloadClient, $b) ? 1 : 0;

        //GET KEY FROM PEM FILE
        $fp = fopen("pkis/PMS/PMS_SSO.pem", "r");
        $cert = fread($fp, 8192);
        fclose($fp);
        //END

        //CHECK 3
        try {
            $check3 = @openssl_verify($b, hex2bin($c), $cert, 'sha1WithRSAEncryption');
        } catch (\Exception $e) {
            $check3 = false;
        }

        if ($check3 && $check1) {
            Auth::loginUsingId($empDetails[0]->Id, true);
            $roleId = (int) Auth::user()->RoleId;
            switch ($roleId):
                case 2: //USER
                    setUserDepartmentAndGrade();
                    return redirect('viewprofile');
                case 1: //ADMIN
                    setUserDepartmentAndGrade();
                    return redirect('departmentindex');
                default:
                    Auth::logout();
                    return redirect('/');
            endswitch;
        } else {
            abort(404);
        }
    }
    
}