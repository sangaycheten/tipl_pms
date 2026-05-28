@extends('master')
@section('page-title','Employees Eligible for Regular')
@section('page-header',"Employees Eligible for Regular")
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 20px;">
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="no-decoration">Filter your search - You can select one filter or a combination of filters to narrow your search.</h6>
                            </div>
                        </div>
                        <form action="" method="GET" id="form-daterestriction">
                            <div class="row">
                                <div class="col-12 col-sm-3">
                                    <div class="form-group">
                                        <label for="DepartmentId" class="control-label">Department</label>
                                        <select name="DepartmentId" class="form-control select2 fetch-employee-on-dept" id="filter-section">
                                            <option value="">All</option>
                                            @foreach($departments as $department)
                                                <option @if($department->Id == Request::get('DepartmentId'))selected="selected"@endif value="{{$department->Id}}">{{$department->ShortName}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" value="1" name="Submitted"/>
                                <div class="col-12 col-sm-6" style="margin-top:8px;">
                                    <br>
                                    <button type="submit" style="" class="btn btn-primary"><i class="fa fa-search"></i> Search</button> &nbsp;
                                    <a href="{{URL::to('eligibleforregularreport')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Clear</a> &nbsp;
                                    &nbsp;<a href="{{ URL::to('eligibleforregularreport') . "?export=excel&" . Request::getQueryString() }}" class="btn btn-success"><i class="fa fa-file-excel-o"></i> &nbsp;Export to Excel</a>
                                </div>
                            </div>
                        </form>

                        <div class="row">
                            <div class="col-md-12">
                                <br>
                                <?php $var = "Round of Last Reward"; ?>
                                <h6 style="text-decoration: none;">Eligible for Regular Promotion According to Grade Step</h6>
                                <div class="table-responsive">
                                    <table class="table table-condensed table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Sl #</th>
                                                <th>Employee</th>
                                                <th>Designation</th>
                                                <th>Grade Step</th>
                                                <th>Last Reward in PMS</th>
                                                <th>Requirement for RSP</th>
                                                <th>Achievement</th>
						<th>Achievement (D.O.R)</th>
						<th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $slNo = 1; ?>
					    @forelse($regularPromotionEligible as $data)
						<?php
                                                    $empId = $data->EmpId;
                                                    $employeeId = $data->Id;
                                                    $employeeSubmissionStatus = DB::select("SELECT A.PMSNumberId, A.PMSSubmissionId, A.PMSResult FROM pms_historical A WHERE A.EmpId = ? ORDER BY A.PMSNumberId DESC LIMIT 1 ", [$empId]);
                                                    $pmsSubmissionId = $employeeSubmissionStatus[0]->PMSSubmissionId;

                                                    $pmsSubmissionDetails = DB::select("SELECT P.Name AS Outcome, A.WithDrawal FROM pms_submission A JOIN mas_pmsoutcome P ON P.Id = A.SavedPMSOutcomeId WHERE A.Id = ? AND A.EmployeeId = ? ", [$pmsSubmissionId, $employeeId]);
						    $pmsOutcome = $pmsSubmissionDetails[0]->Outcome;
						    $withDrawal = (int) $pmsSubmissionDetails[0]->WithDrawal;
                                                ?>
						@if ($withDrawal != 1)
						    <tr>
                                                        <td>{{ $slNo++ }}.</td>
                                                        <td><a href="{{url('viewprofile')}}/{{$data->Id}}" target="_blank">{{$data->Employee}}</a><br>EmpId: {{$data->EmpId}}</td>
                                                        <td>{{$data->Designation}} <br> {{$data->Section}} ({{$data->Department}})</td>
                                                        <td>{{$data->GradeStep}}</td>
                                                        <td>{{$data->$var ? "Round ".$data->$var: ""}}</td>
                                                        <td><em>{{$data->Requirement}}</em></td>
                                                        <td>{{$data->Achieved}}&nbsp;<button type="button" class="btn btn-primary fetch-pms-history btn-xs" data-id="{{$data->Id}}"><i class="fa fa-eye" ></i></button></td>
						        <td>{{$data->AchievementDOR}}&nbsp;</td>
						        <td class="text-center">
							    ({{ $pmsOutcome }})&nbsp;&nbsp;
							    <a class="btn btn-xs btn-warning" href="{{URL::to('finalizepms', [$pmsSubmissionId])}}"><i class="fa fa-mail-forward"></i> Proceed </a>&nbsp;&nbsp;
							    <a class="btn btn-xs btn-danger withdrawal-confirm" href="{{URL::to('withdrawalemployee', [$pmsSubmissionId, $employeeId])}}"><i class="fa fa-remove"></i> Withdrawal</a>&nbsp;&nbsp;
                                                        </td>
						    </tr>
						@endif
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center">No data to display!</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <hr style="border-top: 2px solid #fff;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="pms-history">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">PMS Score History</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
