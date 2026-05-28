@extends('master')
@section('page-title',$update?'Update Appraisal Structure':'Set Appraisal Structure')
@section('page-header',$update?'Update Appraisal Structure':'Set Appraisal Structure')
@section('action-button')
    @parent
    <a href="{{URL::to('hierarchyindex')}}" class="btn btn-success btn-xs viewall-confirm"><i class="fa fa-list"></i> View All</a>
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
                            {{Form::open(['url'=>'savehierarchy'])}}
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
                                        {{--<div class="col-sm-12">--}}
                                        {{Form::hidden("EmployeeId",$hierarchy[0]->Id)}}
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label for="Name" class="control-label">Name</label>
                                                <input type="text" autocomplete="off" autocomplete="off" disabled="disabled" name="Name" value="{{$hierarchy[0]->Employee}}, Emp Id: {{$hierarchy[0]->EmpId}} ({{$hierarchy[0]->Designation}}, {{$hierarchy[0]->Department}})" id="Name" class="form-control"/>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label for="ReportingLevel1EmployeeId" class="control-label">Reports to (Level 1)
                                                    <span class="required">*</span></label>
                                                <select name="ReportingLevel1EmployeeId[]" required="required" class="form-control select2" id="ReportingLevel1EmployeeId">
                                                    <option value="">None</option>
                                                    @foreach($employees as $employee)
                                                        <option @if($employee->Id == $hierarchy[0]->ReportingLevel1EmployeeId)selected="selected"@endif value="{{$employee->Id}}">{{$employee->Name}}, Emp Id: {{$employee->EmpId}} ({{$employee->Designation}}, {{$employee->Department}})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label for="ReportingLevel2EmployeeId" class="control-label">Reports to (Level 2)</label>
                                                <select name="ReportingLevel2EmployeeId[]" class="form-control select2" id="ReportingLevel2EmployeeId">
                                                    <option value="">None</option>
                                                    @foreach($employees as $employee)
                                                        <option @if($employee->Id == $hierarchy[0]->ReportingLevel2EmployeeId)selected="selected"@endif value="{{$employee->Id}}">{{$employee->Name}}, Emp Id: {{$employee->EmpId}} ({{$employee->Designation}}, {{$employee->Department}})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-lg-4 offset-md-6 offset-lg-4">
                                            <div class="form-group">
                                                <label for="ReportingLevel1EmployeeId2" class="control-label">Reports to (Level 1)</label>
                                                <select name="ReportingLevel1EmployeeId[]" class="form-control select2" id="ReportingLevel1EmployeeId2">
                                                    <option value="">None</option>
                                                    @foreach($employees as $employee)
                                                        <option @if($employee->Id == (isset($hierarchy2[0]->ReportingLevel1EmployeeId)?$hierarchy2[0]->ReportingLevel1EmployeeId:''))selected="selected"@endif value="{{$employee->Id}}">{{$employee->Name}}, Emp Id: {{$employee->EmpId}} ({{$employee->Designation}}, {{$employee->Department}})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label for="ReportingLevel2EmployeeId2" class="control-label">Reports to (Level 2)</label>
                                                <select name="ReportingLevel2EmployeeId[]" class="form-control select2" id="ReportingLevel2EmployeeId2">
                                                    <option value="">None</option>
                                                    @foreach($employees as $employee)
                                                        <option @if($employee->Id == (isset($hierarchy2[0]->ReportingLevel2EmployeeId)?$hierarchy2[0]->ReportingLevel2EmployeeId:''))selected="selected"@endif value="{{$employee->Id}}">{{$employee->Name}}, Emp Id: {{$employee->EmpId}} ({{$employee->Designation}}, {{$employee->Department}})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-lg-4 offset-md-6 offset-lg-4">
                                            <div class="form-group">
                                                <label for="ReportingLevel1EmployeeId3" class="control-label">Reports to (Level 1)</label>
                                                <select name="ReportingLevel1EmployeeId[]" class="form-control select2" id="ReportingLevel1EmployeeId3">
                                                    <option value="">None</option>
                                                    @foreach($employees as $employee)
                                                        <option @if($employee->Id == (isset($hierarchy3[0]->ReportingLevel1EmployeeId)?$hierarchy3[0]->ReportingLevel1EmployeeId:''))selected="selected"@endif value="{{$employee->Id}}">{{$employee->Name}}, Emp Id: {{$employee->EmpId}} ({{$employee->Designation}}, {{$employee->Department}})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label for="ReportingLevel2EmployeeId3" class="control-label">Reports to (Level 2)</label>
                                                <select name="ReportingLevel2EmployeeId[]" class="form-control select2" id="ReportingLevel2EmployeeId3">
                                                    <option value="">None</option>
                                                    @foreach($employees as $employee)
                                                        <option @if($employee->Id == (isset($hierarchy3[0]->ReportingLevel2EmployeeId)?$hierarchy3[0]->ReportingLevel2EmployeeId:''))selected="selected"@endif value="{{$employee->Id}}">{{$employee->Name}}, Emp Id: {{$employee->EmpId}} ({{$employee->Designation}}, {{$employee->Department}})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
				    </div>
				    <div class="row">
                                <div class="col-md-6 col-lg-4 offset-md-6 offset-lg-4">
                                    <div class="form-group">
                                        <label for="ReportingLevel1EmployeeId3" class="control-label">Reports to (Level 1)</label>
                                        <select name="ReportingLevel1EmployeeId[]" class="form-control select2" id="ReportingLevel1EmployeeId3">
                                            <option value="">None</option>
                                            @foreach ($employees as $employee)
                                                <option @if ($employee->Id == (isset($hierarchy4[0]->ReportingLevel1EmployeeId) ? $hierarchy4[0]->ReportingLevel1EmployeeId : '')) selected="selected" @endif
                                                    value="{{ $employee->Id }}">{{ $employee->Name }}, EmpId: {{ $employee->EmpId }} ({{ $employee->Designation }}, {{ $employee->Department }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label for="ReportingLevel2EmployeeId3" class="control-label">Reports to (Level 2)</label>
                                        <select name="ReportingLevel2EmployeeId[]" class="form-control select2" id="ReportingLevel2EmployeeId3">
                                            <option value="">None</option>
                                            @foreach ($employees as $employee)
                                                <option @if ($employee->Id == (isset($hierarchy4[0]->ReportingLevel2EmployeeId) ? $hierarchy4[0]->ReportingLevel2EmployeeId : '')) selected="selected" @endif
                                                    value="{{ $employee->Id }}">{{ $employee->Name }}, EmpId: {{ $employee->EmpId }} ({{ $employee->Designation }}, {{ $employee->Department }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
			    </div>

                                    <div class="col-lg-4 col-md-4 col-sm-4 col-8">
                                        <div class="row" >
                                            <div class="col-lg-12 col-md-12 col-sm-12">
                                                <button type="submit" style="" class="btn btn-primary">Save</button> &nbsp;
                                                <a href="{{URL::to('hierarchyindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
