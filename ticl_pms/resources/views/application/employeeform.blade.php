@extends('master')
@section('page-title',$update?'Update Employee':'Add Employee')
@section('page-header',$update?'Update Employee':'Add Employee')
@section('action-button')
    @parent
    <a href="{{URL::to('employeeindex')}}" class="btn btn-success btn-xs viewall-confirm"><i class="fa fa-list"></i> View All</a>
@endsection
@section('content')
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-12 card dashboard-product">
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
                        {{Form::open(['url'=>'saveemployee','id'=>'change-pw-form'])}}
                            {{Form::hidden('Id',isset($employee['Id'])?$employee['Id']:old('Id'))}}
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
                                <div class="col-md-3 col-xs-12">
                                    <div class="form-group">
                                        <label for="Name">Name <span class="required">*</span></label>
                                        <input type="text" autocomplete="off" autocomplete="off" required="required" id="Name" name="Name" value="{{isset($employee['Name'])?$employee['Name']:old('Name')}}" autocomplete="off" class="form-control"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="Gender">Gender<span class="required">*</span></label>
                                        {{Form::select("Gender",['M'=>'Male','F'=>'Female'],isset($employee['Gender'])?$employee['Gender']:old('Gender'),['class'=>'form-control','id'=>'Gender'])}}
                                    </div>
                                    <div class="form-group">
                                        <label for="EmpId">Employee Id <span class="required">*</span></label>
                                        <input type="number" onkeydown="return event.keyCode !== 69" autocomplete="off" required="required" id="EmpId" name="EmpId" value="{{isset($employee['EmpId'])?$employee['EmpId']:old('EmpId')}}" autocomplete="off" class="form-control"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="CIDNo">CID <span class="required">*</span></label>
                                        <input type="text" autocomplete="off" autocomplete="off" required="required" id="CIDNo" name="CIDNo" value="{{isset($employee['CIDNo'])?$employee['CIDNo']:old('CIDNo')}}" autocomplete="off" class="form-control"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="Email">Email <span class="required">*</span></label>
                                        <input type="text" autocomplete="off" required="required" id="Email" name="Email" value="{{isset($employee['Email'])?$employee['Email']:old('Email')}}" autocomplete="off" class="form-control"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="Qualification1">Qualification 1</label>
                                        <input type="text" autocomplete="off" id="Qualification1" name="Qualification1" value="{{isset($employee['Qualification1'])?$employee['Qualification1']:old('Qualification1')}}" autocomplete="off" class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-3 col-xs-12">
                                    <div class="form-group">
                                        <label for="Extension">Extension </label>
                                        <input type="number" onkeydown="return event.keyCode !== 69" autocomplete="off" id="Extension" name="Extension" value="{{isset($employee['Extension'])?$employee['Extension']:old('Extension')}}" autocomplete="off" class="form-control"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="MobileNo">Mobile No <span class="required">*</span></label>
                                        <input type="number" onkeydown="return event.keyCode !== 69" autocomplete="off" required="required" id="MobileNo" name="MobileNo" value="{{isset($employee['MobileNo'])?$employee['MobileNo']:old('MobileNo')}}" autocomplete="off" class="form-control"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="DepartmentId">Department<span class="required">*</span></label>
                                        <select name="DepartmentId" id="filter-section" class="form-control select2" required="required">
                                            <option value="">--SELECT ONE--</option>
                                            @foreach($departments as $department)
                                                <option value="{{$department->Id}}" @if($department->Id == (isset($employee['DepartmentId'])?$employee['DepartmentId']:old('DepartmentId')))selected="selected"@endif >{{$department->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="SectionId">Section </label>
                                        <select name="SectionId" id="select-department" class="form-control select2">
                                            <option value="">--SELECT ONE--</option>
                                            @foreach($sections as $section)
                                                <option value="{{$section->Id}}" data-departmentid="{{$section->DepartmentId}}" @if($section->Id == (isset($employee['SectionId'])?$employee['SectionId']:old('SectionId')))selected="selected"@endif >{{$section->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="JobLocation">Job Location <span class="required">*</span></label>
                                        <input type="text" autocomplete="off" value="{{isset($employee['JobLocation'])?$employee['JobLocation']:old('JobLocation')}}" autocomplete="off" required="required" id="JobLocation" name="JobLocation" autocomplete="off" class="form-control"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="Qualification2">Qualification 2 </label>
                                        <input type="text" autocomplete="off" id="Qualification2" name="Qualification2" value="{{isset($employee['Qualification2'])?$employee['Qualification2']:old('Qualification2')}}" autocomplete="off" class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-3 col-xs-12">
                                    {{--<div class="form-group">--}}
                                        {{--<label for="PositionId">Evaluation Group<span class="required">*</span></label>--}}
                                        {{--<select name="PositionId" id="PositionId" class="form-control select2" required="required">--}}
                                            {{--<option value="">--SELECT ONE--</option>--}}
                                            {{--@foreach($positions as $position)--}}
                                                {{--<option value="{{$position->Id}}" @if($position->Id == (isset($employee['PositionId'])?$employee['PositionId']:old('PositionId')))selected="selected"@endif >{{$position->Name}}</option>--}}
                                            {{--@endforeach--}}
                                        {{--</select>--}}
                                    {{--</div>--}}
                                    <div class="form-group">
                                        <label for="DesignationId">Designation<span class="required">*</span></label>
                                        <select name="DesignationId" id="DesignationId" class="form-control select2" required="required">
                                            <option value="">--SELECT ONE--</option>
                                            @foreach($designationLocations as $designationLocation)
                                                <option value="{{$designationLocation->Id}}" @if($designationLocation->Id == (isset($employee['DesignationId'])?$employee['DesignationId']:old('DesignationId')))selected="selected"@endif >{{$designationLocation->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="DateOfBirth">Date of Birth <span class="required">*</span></label>
                                        <input type="date" value="{{isset($employee['DateOfBirth'])?$employee['DateOfBirth']:old('DateOfBirth')}}" autocomplete="off" required="required" id="DateOfBirth" name="DateOfBirth" autocomplete="off" class="form-control"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="DateOfAppointment">Date of Appointment <span class="required">*</span></label>
                                        <input type="date" value="{{isset($employee['DateOfAppointment'])?$employee['DateOfAppointment']:old('DateOfAppointment')}}" autocomplete="off" required="required" id="DateOfAppointment" name="DateOfAppointment" autocomplete="off" class="form-control"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="GradeStepId">Grade / Step<span class="required">*</span></label>
                                        <select name="GradeStepId" id="GradeStepId" class="form-control populate-basic-pay select2" required="required">
                                            <option value="">--SELECT ONE--</option>
                                            @foreach($gradeSteps as $gradeStep)
                                                <option value="{{$gradeStep->Id}}" data-basicpay="{{doubleval(substr($gradeStep->PayScale,0,strpos($gradeStep->PayScale,'-')-1))}}" @if($gradeStep->Id == (isset($employee['GradeStepId'])?$employee['GradeStepId']:old('GradeStepId')))selected="selected"@endif >{{$gradeStep->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="CriteriaMainId">Main Group <span class="required">*</span></label>
                                        <select name="CriteriaMainId" id="CriteriaMainId" required="required" class="form-control select2"> <!-- is actually from mas_grade -->
                                            <option value="">--SELECT--</option>
                                            @foreach($grades as $grade)
                                                <option value="{{$grade->Id}}" @if($grade->Id == (isset($employee['CriteriaMainId'])?$employee['CriteriaMainId']:old('CriteriaMainId'))) selected="selected"@endif>{{$grade->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="GradeId">Sub Group </label>
                                        <select name="SupervisorId" id="SupervisorId" class="form-control select2">
                                            <option value="">--SELECT--</option>
                                            @foreach($supervisorLevels as $supervisorLevel)
                                                <option value="{{$supervisorLevel->Id}}" @if($supervisorLevel->Id == (isset($employee['SupervisorId'])?$employee['SupervisorId']:old('SupervisorId'))) selected="selected"@endif>{{$supervisorLevel->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-xs-12">
                                    <div class="form-group">
                                        <label for="BasicPay">Basic Pay <span class="required">*</span></label>
                                        <input type="number" onkeydown="return event.keyCode !== 69" value="{{isset($employee['BasicPay'])?str_replace(",","",$employee['BasicPay']):old('BasicPay')}}" autocomplete="off" required="required" id="BasicPay" name="BasicPay" autocomplete="off" class="form-control"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="Status">Status<span class="required">*</span></label>
                                        {{Form::select("Status",['3'=>'Probation','4'=>'Suspended','5'=>'Terminated','1'=>'Regular/Contract','2'=>'In-active (EOL, Study Leave, etc)','0'=>'Resigned'],isset($employee['Status'])?$employee['Status']:old('Status'),['class'=>'form-control','id'=>'Status'])}}
                                    </div>
                                    @if(!$update)
                                        <div class="form-group">
                                            <label for="12">Password <span class="required">*</span></label>
                                            <input type="password" required="required" id="new-pw" autocomplete="off" class="form-control"/>
                                        </div>
                                        <div class="form-group">
                                            <label for="Password">Confirm Password <span class="required">*</span></label>
                                            <input type="password" required="required" id="new-pw-1" name="Password" autocomplete="off" class="form-control"/>
                                        </div>
                                    @endif

                                    <div class="form-group">
                                        <label for="RoleId">System Role<span class="required">*</span></label>
                                        <select name="RoleId" id="RoleId" class="form-control select2" required="required">
                                            <option value="">--SELECT ONE--</option>
                                            @foreach($roles as $role)
                                                <option value="{{$role->Id}}" @if($role->Id == (isset($employee['RoleId'])?$employee['RoleId']:old('RoleId')))selected="selected"@endif >{{$role->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="GradeId">Organization Role</label>
                                        <select name="GradeId" id="GradeId" class="form-control select2">
                                            <option value="">Other</option>
                                            @foreach($grades as $grade)
                                                <option value="{{$grade->Id}}" @if($grade->Id == (isset($employee['GradeId'])?$employee['GradeId']:old('GradeId'))) selected="selected"@endif>{{$grade->Name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="dont-disable btn btn-primary">{{$update?'Update':'Add'}}</button>
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
