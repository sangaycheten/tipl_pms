<?php
    function randomString(){
        $possible = "aBcDeFgHiJkLmNoPqRsTuVwXyZAbCdEfGhIjKlMnOpQrStUvWxYz123456789";
        $randomString = "";
        while(strlen($randomString)<5){
            $strShuffled = str_shuffle($possible);
            $randomCharacter = substr($strShuffled,rand(0,59),1);
            if((bool)$randomCharacter){
                $randomString .= $randomCharacter;
            }
        }
        return $randomString;
    }
    function UUID(){
        $uuidQuery = DB::select("select UUID() as Id");
        $uuid = $uuidQuery[0]->Id;
        return $uuid;
    }
    function convertDateTimeToClientFormat($date){
        $newDate = date_create($date);
        return date_format($newDate,'jS M, Y \a\t h:i A');
    }
    function convertDateToClientFormat($date){
        $newDate = date_create($date);
        return date_format($newDate,'jS M, Y');
    }
    function convertNumberToWord($number){
        $number = (int)$number;
        switch($number):
            case 1: return 'one';
            case 2: return 'two';
            case 3: return 'three';
            case 4: return 'four';
            case 5: return 'five';
            case 6: return 'six';
            case 7: return 'seven';
            case 8: return 'eight';
            case 9: return 'nine';
            case 10: return 'ten';
            case 11: return 'eleven';
            case 12: return 'twelve';
            case 13: return 'thirteen';
            case 14: return 'fourteen';
            case 15: return 'fifteen';
            case 16: return 'sixteen';
            case 17: return 'seventeen';
            case 18: return 'eighteen';
            case 19: return 'nineteen';
            case 20: return 'twenty';
            case 21: return 'twentyone';
            case 22: return 'twentytwo';
            case 23: return 'twentythree';
            case 24: return 'twentyfour';
            case 25: return 'twentyfive';
            case 26: return 'twentysix';
            case 27: return 'twentyseven';
            case 28: return 'twentyeight';
            case 29: return 'twentynine';
            case 30: return 'thirty';
            case 31: return 'thirtyone';
            case 32: return 'thirtytwo';
            case 33: return 'thirtythree';
            case 34: return 'thirtyfour';
            case 35: return 'thirtyfive';
            case 36: return 'thirtysix';
            case 37: return 'thirtyseven';
            case 38: return 'thirtyeight';
            case 39: return 'thirtynine';
            case 40: return 'forty';
            case 41: return 'fortyone';
            case 42: return 'fortytwo';
            case 43: return 'fortythree';
            case 44: return 'fortyfour';
            case 45: return 'fortyfive';
            case 46: return 'fortysix';
            case 47: return 'fortyseven';
            case 48: return 'fortyeight';
            case 49: return 'fortynine';
            case 50: return 'fifty';
            case 51: return 'fiftyone';
            case 52: return 'fiftytwo';
            case 53: return 'fiftythree';
            case 54: return 'fiftyfour';
            case 55: return 'fiftyfive';
            case 56: return 'fiftysix';
            case 57: return 'fiftyseven';
            case 58: return 'fiftyeight';
            case 59: return 'fiftynine';
            case 60: return 'sixty';
            case 61: return 'sixtyone';
            case 62: return 'sixtytwo';
            case 63: return 'sixtythree';
            case 64: return 'sixtyfour';
            case 65: return 'sixtyfive';
            case 66: return 'sixtysix';
            case 67: return 'sixtyseven';
            case 68: return 'sixtyeight';
            case 69: return 'sixtynine';
            case 70: return 'seventy';
            case 71: return 'seventyone';
            case 72: return 'seventytwo';
            default: return 'thirty';
        endswitch;
    }
    function setUserDepartmentAndGrade(){
        $department = DB::table('mas_department')->where('Id',Auth::user()->DepartmentId)->pluck('Name');
        $gradeId = DB::table('mas_employee as T1')->join('mas_gradestep as T2','T2.Id','=','T1.GradeStepId')->where('T1.Id',Auth::user()->Id)->pluck('T2.GradeId');
        $gradeId = isset($gradeId[0])?$gradeId[0]:NULL;
        Session::put('UserDepartment',$department[0]);
        Session::put('GradeId',$gradeId);
    }
    function arrayToString($array,$oldKey = null){
        $string = '';
        foreach($array as $key=>$value):
            if(gettype($value) == 'array'){
                $string.=arrayToString($value,$key);
            }else{
                if((bool)$oldKey){
                    $string .= "<br/>$oldKey:$value";
                }else{
                    $string .= "<br/>$key:$value";
                }
            }
        endforeach;
        return ($string=='')?'[]':$string;
    }
    function getPMSDetails(){
        $year = 2007;
        $counter = 0;
        for($i=$year; $i<2019; $i++):
            $counter += 1;
            DB::table('sys_pmsnumber')->insert(['StartDate'=>"$i-01-01",'PMSNumber'=>$counter]);
            $counter += 1;
            DB::table('sys_pmsnumber')->insert(['StartDate'=>"$i-07-01",'PMSNumber'=>$counter]);
        endfor;
    }
