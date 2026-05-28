@extends('master')
@section('page-title',$update?'Update Disciplinary Record':'Add Disciplinary Record')
@section('page-header',$update?'Update Disciplinary Record':'Add Disciplinary Record')
@section('action-button')
    @parent
    <a href="{{URL::to('disciplinaryindex')}}" class="btn btn-success btn-xs viewall-confirm"><i class="fa fa-list"></i> View All</a>
@endsection
@section('content')
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-8 card dashboard-product">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if(Session::has('message'))
                            <h6><i class="fa fa-times-circle" style="color:red"></i> {!!Session::get('message')!!}</h6>
                        @endif
                        {{Form::open(['url'=>'savedisciplinary'])}}
                            {{Form::hidden('Id',isset($disciplinaryRecord['Id'])?$disciplinaryRecord['Id']:old('Id'))}}
                            @if(Input::has('redirect'))
                                <?php
                                $append = "";
                                ?>
                                @foreach(Input::except('redirect') as $key=>$value)
                                    <?php
                                    if(gettype($value)=='array'){
                                        foreach($value as $x=>$y):
                                            if($append == ''):
                                                $append.="?";
                                            else:
                                                $append.="&";
                                            endif;
                                            $append.="$key"."[]"."=$y";
                                        endforeach;
                                    }else{
                                        if($append == ''):
                                            $append.="?";
                                        else:
                                            $append.="&";
                                        endif;
                                        $append.="$key=$value";
                                    }
                                    $append = urlencode($append);
                                    ?>
                                @endforeach
                                {{Form::hidden('redirect',Input::get('redirect').$append)}}
                            @endif
                            <div class="row">
                                <div class="col-md-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="DepartmentId">Department <span class="required">*</span></label>
                                        <select name="DepartmentId" id="fetch-employees-dept" class="form-control select2" required="required">
                                            <option value="">--SELECT ONE--</option>
                                            @foreach($departments as $department)
                                                <option value="{{$department->Id}}" @if($department->Id == (isset($disciplinaryRecord['DepartmentId'])?$disciplinaryRecord['DepartmentId']:old('DepartmentId')))selected="selected"@endif >{{$department->ShortName}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="ActionTakenBy">Action Taken By <span class="required">*</span></label>
                                        <input type="text" autocomplete="off" required="required" name="ActionTakenBy" value="{{isset($disciplinaryRecord['ActionTakenBy'])?$disciplinaryRecord['ActionTakenBy']:old('ActionTakenBy')}}" id="ActionTakenBy" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="EmployeeId">Employee <span class="required">*</span></label>
                                        <select name="EmployeeId" id="fetched-employees" class="form-control select2" required="required">
                                            <option value="">--SELECT ONE--</option>
                                            @foreach($employees as $employee)
                                                <option value="{{$employee->Id}}" @if($employee->Id == $disciplinaryRecord['EmployeeId'])selected="selected"@endif>{{$employee->Name}} - CID No.: {{$employee->CIDNo}}, Emp Id: {{$employee->EmpId}} ({{$employee->Designation}})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="RecordDate">Action Taken On <span class="required">*</span></label>
                                        <input type="date" autocomplete="off" required="required" name="RecordDate" value="{{isset($disciplinaryRecord['RecordDate'])?$disciplinaryRecord['RecordDate']:old('RecordDate')}}" id="RecordDate" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="Record">Offense <span class="required">*</span></label>
                                        <input type="text" autocomplete="off" autocomplete="off" required="required" name="Record" value="{{isset($disciplinaryRecord['Record'])?$disciplinaryRecord['Record']:old('Record')}}" id="Record" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 col-xs-12">
                                    <div class="form-group">
                                        <label for="RecordDescription">Action Taken <span class="required">*</span></label>
                                        <textarea autocomplete="off" required="required" rows="3" name="RecordDescription" id="RecordDescription" class="form-control">{{isset($disciplinaryRecord['RecordDescription'])?$disciplinaryRecord['RecordDescription']:old('RecordDescription')}}</textarea>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">{{$update?'Update':'Add'}}</button>
                            @if(Input::has('redirect'))
                                <a href="{{URL::to(Input::get('redirect').$append)}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                            @else
                                <a href="{{URL::to('employeeindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                            @endif
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop