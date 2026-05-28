@extends('master')
@section('page-title','Regions')
@section('page-header',"Manage Regions Criteria")
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-12 col-lg-12" style="padding-bottom:0;">
                                <div class="row">
                                    <div class="col-md-3 col-sm-9 text-left" style="padding-bottom:0;">
                                        <a href="{{URL::to('regioncriteriainput')}}" class="btn btn-success btn-xs"><i class="fa fa-plus"></i> Add </a>
                                        <br><br>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-10">
                                <h6 class="no-decoration">Filter your search - You can select one filter or a combination of filters to narrow your search.</h6>
                            </div>
                        </div>
                        <form action="" method="GET">
                            <div class="row">
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="DepartmentId" class="control-label">Department</label>
                                        <select name="DepartmentId" class="form-control select2" id="DepartmentId">
                                            <option value="">All</option>
                                            @foreach($departments as $department)
                                                <option @if($department->Id == Input::get('DepartmentId'))selected="selected"@endif value="{{$department->Id}}">{{$department->ShortName}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="form-group">
                                        <label for="EmployeeName" class="control-label">Employee Name</label>
                                        <input type="text" autocomplete="off" name="EmployeeName" value="{{Input::get('EmployeeName')}}" id="EmployeeName" class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-2">
                                    <div class="form-group">
                                        <label for="EmployeeId" class="control-label">Employee ID</label>
                                        <input type="text" autocomplete="off" name="EmployeeId" value="{{Input::get('EmployeeId')}}" id="EmployeeId" class="form-control"/>
                                    </div>
                                </div>
                                <input type="hidden" value="1" name="Submitted"/>
                                <div class="col-lg-3 col-md-4 col-sm-4 col-8">
                                    <div class="row" style="margin-top:30px;">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <button type="submit" style="" class="btn btn-primary"><i class="fa fa-search"></i> Search</button> &nbsp;
                                            <a href="{{URL::to('regioncriteriaindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Clear</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                <table class="table table-striped table-condensed table-bordered font-small">
                                    <thead>
                                        <tr>
                                            <th>Sl#</th>
                                            <th>Employee</th>
                                            <th>Section</th>
                                            <th>Department</th>
                                            <th>Level 1 (Appraiser)</th>
					    <th>Level 2 (Appraiser)</th>
					    <th>Employee Status</th>
                                            <th class="text-center" style="width:20%;">Actions</th>
                                        </tr>
                                    </thead>
                                    <?php $slNo=Input::has('page')?(Input::get('page')-1)*$perPage+1:1; ?>
                                    @forelse($regionsemployees as $region)
                                        <tr>
                                            <td>{{$slNo++}}. </td>
                                            <td><a href="{{url('viewprofile',[$region->EmployeeId])}}" target="_blank">{{$region->EmployeeName}} ({{$region->EmpId}}, {{$region->Designation}})</a></td>
                                            <td>{{$region->Section}}</td>
                                            <td>{{$region->Department}}</td>
                                            <td>{!! $region->Level1 !!}</td>
					    <td>{!! $region->Level2 !!}</td>
					    <td>
                                                @if ($region->EmployeeStatus == 1)
                                                    Regular/Contract
                                                @elseif($region->EmployeeStatus == 2)
                                                    In-active
                                                @elseif($region->EmployeeStatus == 3)
                                                    Probation
                                                @elseif($region->EmployeeStatus == 4)
                                                    Suspended
                                                @elseif($region->EmployeeStatus == 5)
                                                    Terminated
                                                @else
                                                    Resigned
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a class="btn btn-primary btn-xs editconfirm" href="{{URL::to('regioncriteriainput',[$region->EmployeeId])}}"><i class="fa fa-edit"></i> Edit</a>&nbsp;&nbsp;
                                                <a class="btn btn-danger btn-xs deleteconfirm" href="{{URL::to('regioncriteriadelete',[$region->Id])}}"><i class="fa fa-times"></i> Delete</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center"><strong>No data to display.</strong></td>
                                        </tr>
                                    @endforelse
                                </table>
                                    {{$regionsemployees->appends(Input::except('page'))->links()}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
