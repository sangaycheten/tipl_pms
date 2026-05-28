@extends('master')
@section('page-title',$update?'Update Regions Criteria':'Set Regions Criteria')
@section('page-header',$update?'Update Regions Criteria':'Set Regions Criteria')
@section('action-button')
    @parent
    <a href="{{URL::to('regioncriteriaindex')}}" class="btn btn-success btn-xs viewall-confirm"><i class="fa fa-list"></i> View All</a>
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
			    {{Form::open(['url'=>'saveregioncriteria'])}}
			    {{-- <form action="{{ route('saveregioncriteria') }}" method="POST"> --}}
                              	{{-- @csrf --}}
                            @if ($update == true)
                                <input type="hidden" name="Id" value={{($regionemployees['Id']??old('Id'))}} />
                                <div class="row">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="EmployeeId" class="control-label">Employee (Appraisee) <span class="required">*</span></label></label>
                                            <select name="EmployeeId" class="form-control select2" id="EmployeeId">
                                                <option value=""> -- SELECT ONE -- </option>
                                                @foreach($employees as $employee)
                                                    <option @if($employee->Id == ($regionemployees['EmployeeId']??old('EmployeeId')))selected="selected"@endif value="{{$employee->Id}}">{{$employee->Name}}, EmpId: {{$employee->EmpId}} ({{$employee->Designation}}, {{$employee->Department}})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="Level1ANDWeightage" class="control-label">Level1 AND (Weightage) <span class="required">*</span></label>
                                            <input type="number" step="0.01" id="Level1ANDWeightage" name="Level1ANDWeightage" value="{{isset($regionemployees['Level1ANDWeightage'])?$regionemployees['Level1ANDWeightage']:old('Level1ANDWeightage')}}" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="Level1ANDAppraiserId" class="control-label">Level1 AND (Appraiser) <span class="required">*</span></label></label>
                                            <select name="Level1ANDAppraiserId[]" multiple="multiple" class="form-control select2" id="Level1ANDAppraiserId" required="required">
                                                <option value=""> -- SELECT ONE -- </option>
                                                @foreach($level1employees as $level1)
                                                    @if ($level1->DepartmentId == 4)
                                                        <option value="{{$level1->Id}}" @if(in_array($level1->Id, $regionAndMaps))selected="selected" @endif >{{$level1->EmployeeName}}, EmpId: {{$level1->EmpId}} ({{$level1->Designation}}, {{$level1->Department}})</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="Level1MarketingWeightage" class="control-label">Level1 Marketing (Weightage) <span class="required">*</span></label>
                                            <input type="number" step="0.01" id="Level1MarketingWeightage" name="Level1MarketingWeightage" value="{{isset($regionemployees['Level1MarketingWeightage'])?$regionemployees['Level1MarketingWeightage']:old('Level1MarketingWeightage')}}" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="Level1MarketingAppraiserId" class="control-label">Level1 Marketing (Appraiser) <span class="required">*</span></label></label>
                                            <select name="Level1MarketingAppraiserId[]" multiple="multiple" class="form-control select2" id="Level1MarketingAppraiserId">
                                                <option value=""> -- SELECT ONE -- </option>
                                                @foreach($level1employees as $level1)
                                                    @if ($level1->DepartmentId == 2)
                                                        <option value="{{$level1->Id}}" @if(in_array($level1->Id, $regionMarketingMaps))selected="selected" @endif >{{$level1->EmployeeName}}, EmpId: {{$level1->EmpId}} ({{$level1->Designation}}, {{$level1->Department}})</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="EmployeeId" class="control-label">Employee (Appraisee) <span class="required">*</span></label></label>
                                            <select name="EmployeeId" class="form-control select2" id="EmployeeId">
                                                <option value=""> -- SELECT ONE -- </option>
                                                @foreach($employees as $employee)
                                                    <option value="{{$employee->Id}}">{{$employee->Name}}, EmpId: {{$employee->EmpId}} ({{$employee->Designation}}, {{$employee->Department}})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="Level1ANDWeightage" class="control-label">Level1 AND (Weightage) <span class="required">*</span></label>
                                            <input type="number" step="0.01" id="Level1ANDWeightage" name="Level1ANDWeightage" value="0.00" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="Level1ANDAppraiserId" class="control-label">Level1 AND (Appraiser) <span class="required">*</span></label></label>
                                            <select name="Level1ANDAppraiserId[]" multiple="multiple" class="form-control select2" id="Level1ANDAppraiserId">
                                                <option value=""> -- SELECT ONE -- </option>
                                                @foreach($level1employees as $level1)
                                                    @if ($level1->DepartmentId == 4)
                                                        <option value="{{$level1->Id}}">{{$level1->EmployeeName}}, EmpId: {{$level1->EmpId}} ({{$level1->Designation}}, {{$level1->Department}})</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="Level1MarketingWeightage" class="control-label">Level1 Marketing (Weightage) <span class="required">*</span></label>
                                            <input type="number" step="0.01" id="Level1MarketingWeightage" name="Level1MarketingWeightage" value="0.00" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="Level1MarketingAppraiserId" class="control-label">Level1 Marketing (Appraiser) <span class="required">*</span></label></label>
                                            <select name="Level1MarketingAppraiserId[]" multiple="multiple" class="form-control select2" id="Level1MarketingAppraiserId">
                                                <option value=""> -- SELECT ONE -- </option>
                                                @foreach($level1employees as $level1)
                                                    @if ($level1->DepartmentId == 2)
                                                        <option value="{{$level1->Id}}">{{$level1->EmployeeName}}, EmpId: {{$level1->EmpId}} ({{$level1->Designation}}, {{$level1->Department}})</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endif
                                <div class="row" >
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <button type="submit" style="" class="btn btn-primary"><i class="fa fa-send"></i> {{$update?'Update':'Save'}}</button> &nbsp;&nbsp;
                                        <a href="{{URL::to('regioncriteriaindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>&nbsp;&nbsp;
                                    </div>
                                </div>
			    {{-- </form> --}}
			 {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

