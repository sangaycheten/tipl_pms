<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018-12-29
 * Time: 10:58 AM
 */
namespace App\Http\Controllers\Application;

use Illuminate\Http\Request;
use \App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB as DB; //DB (query builder)
use Auth;
use Session;
use Hash;

class AuthController extends Controller
{
    public function getLogin()
    {
        return view('authentication.login');
    }

    public function postAuth()
    {
        $redirect = request('redirect');
        $empId = request('EmpId');
        $empId = trim($empId);

        $password = request('Password');
        $prefix = substr($empId, 0, 3);
        if ($prefix != 'E00') {
            $message = 'User with this Username is not found in our records.';
            if ((bool) $redirect) {
                return redirect('/?redirect=' . $redirect)->with('errormessage', $message);
            } else {
                return back()->with('errormessage', $message);
            }
        } else {
            $empId = str_replace("E00", "", $empId);
        }

        $authenticated = Auth::attempt(['EmpId' => $empId, 'password' => $password, 'Status' => 1], request()->has('RememberMe'));

        if (!$authenticated) {
            $authenticated = Auth::attempt(['EmpId' => $empId, 'password' => $password, 'Status' => 3], request()->has('RememberMe'));
        }

        if ($authenticated) {
            //CHECK ROLE AND REDIRECT
            $roleId = (int) Auth::user()->RoleId;
            switch ($roleId):
                case 2: //USER
                    setUserDepartmentAndGrade();
                    return redirect($redirect ? $redirect : 'viewprofile');
                    break;
                case 1: //ADMIN
                    setUserDepartmentAndGrade();
                    return redirect($redirect ? $redirect : 'departmentindex');
                    break;
                default:
                    Auth::logout();
                    return redirect($redirect ? ('/?redirect=' . $redirect) : '/');
            endswitch;
        } else {
            $existsUser = DB::table('mas_employee')->where('EmpId', $empId)->count();
            if ($existsUser == 0) {
                $message = 'User with this Username is not found in our records.';
            } else {
                $status = DB::table('mas_employee')->where('EmpId', $empId)->pluck("Status");
                if ($status[0] == 0) {
                    $message = "Your account has been deactivated.";
                } else {
                    $message = 'You have entered the wrong password.';
                }
            }
            if ((bool) $redirect) {
                return redirect('/?redirect=' . $redirect)->with('errormessage', $message);
            } else {
                return back()->with('errormessage', $message);
            }
        }
    }

    public function getLogout()
    {
        Session::flush();
        Auth::logout();
        return redirect('/')->with('successmessage', 'You have logged out of TashiCell Online PMS.');
    }

    public function getLogoutAndRedirect()
    {
        Session::flush();
        Auth::logout();
        return redirect('https://ticl-sso.tashicell.com')->with('successmessage', 'You have logged out of TashiCell Online PMS.');
    }

    public function getChangePassword()
    {
        return view('authentication.userchangepassword', ['message' => false]);
    }

    public function postCheckPassword()
    {
        $oldPassword = request('OldPassword');
        $empId = Auth::user()->EmpId;
        $passwordCorrect = Auth::once(['EmpId' => $empId, 'password' => $oldPassword]);
        if ($passwordCorrect) {
            return redirect('newpassword')->with('success', true);
        } else {
            return redirect('changepassword')->with('message', 'Wrong Password! Please try again');
        }
    }

    public function getNewPassword()
    {
        if (!Session::has('success')) {
            return redirect('changepassword');
        }
        return view('authentication.newpassword');
    }

    public function postUpdatePassword()
    {
        $newPassword = request('NewPassword');
        $hashedPassword = Hash::make($newPassword);
        DB::table('mas_employee')->where('Id', Auth::user()->Id)->update(['password' => $hashedPassword]);

        $mailBody = "<p>Dear " . Auth::user()->Name . "<br/><br/>Your password has successfully been changed. If you weren't the one who changed the password, please contact HR Administrator. </p>";
        $email = Auth::user()->Email;
        if ((bool) $email && $email != '@tashicell.com') {
            $this->sendMail($email, $mailBody, 'Online PMS Password has been changed successfully!');
        }

        Session::flush();
        Auth::logout();
        return redirect('/')->with('successmessage', 'Password has been changed successfully! Please login again.');
    }

    public function forgotPassword(Request $request)
    {
        $username = $request->username;
        $cid = $request->cid;

        $prefix = substr($username, 0, 3);
        if ($prefix != 'E00') {
            $message = 'User with this Username is not found in our records.';
            if ((bool) $username) {
                return redirect('/')->with('errormessage', $message);
            } else {
                return back()->with('errormessage', $message);
            }
        } else {
            $username = str_replace("E00", "", $username);
        }

        $user = DB::table('mas_employee')->where('EmpId', trim($username))->take(1)->get(['Id', 'CIDNo', 'DateOfBirth', 'MobileNo', 'Email']);
        if (count($user) > 0):
            $userCid = $user[0]->CIDNo;
            if ($cid != $userCid) {
                return redirect('/')->with('errormessage', 'The CID No. you provided did not match the Employee Id. Please check and try again');
            }
            $userId = $user[0]->Id;
            $email = $user[0]->Email;
            $mobileNo = $user[0]->MobileNo;

            if (!(bool) $email && !(bool) $mobileNo) {
                return redirect('/')->with('errormessage', 'No matching user found! Please try again');
            }
            $newPassword = randomString();
            $hashed = Hash::make($newPassword);
            DB::table('mas_employee')->where('Id', $userId)->update(['password' => $hashed]);
            if ((bool) $email && $email != '@tashicell.com') {
                $this->sendMail($email, "Your password has been successfully reset to $newPassword", "Online PMS Password Reset Successful!");
            }
            if ((bool) $mobileNo) {
                $this->sendSMS($mobileNo, "Your password for online PMS has been successfully reset to $newPassword");
            }
            return redirect('/')->with('successmessage', 'Your password has been reset and sent to you by Email and SMS.');
        else:
            return redirect('/')->with('errormessage', 'Employee with this Employee Id was not found. Please check and try again');
        endif;
    }

    public function getLoginAs($id)
    {
        Auth::loginUsingId($id, true);
        $roleId = (int) Auth::user()->RoleId;
        switch ($roleId):
            case 2: //USER
                setUserDepartmentAndGrade();
                return redirect('viewprofile');
                break;
            case 1: //ADMIN
                setUserDepartmentAndGrade();
                return redirect('departmentindex');
                break;
            default:
                Auth::logout();
                return redirect('/');
        endswitch;
    }

    public function getSigninFromExternal(Request $request)
    {
        $uid = $request->input('uid');
        $userId = substr($uid, 2, strlen($uid) - 4);
        Auth::loginUsingId($userId, true);
        $roleId = (int) Auth::user()->RoleId;
        Session::put('FromSingleSignIn', 1);
        
        switch ($roleId):
            case 2: //USER
                setUserDepartmentAndGrade();
                return redirect('viewprofile');
                break;
            case 1: //ADMIN
                setUserDepartmentAndGrade();
                return redirect('departmentindex');
                break;
            default:
                Auth::logout();
                return redirect('/');
        endswitch;
    }

}
