@extends('master')
@section('page-title', 'Audit Employees PMS Submission')
@section('page-header', 'Audit Employees PMS Submission')
@section('content')
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-12">
                                <br/>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-condensed large-padding font-small" style="margin-bottom:0;">
                                        <thead>
                                            <tr>
                                                <th style="width: 5%;">Sl#</th>
                                                <th style="width: 35%;">Employee</th>
                                                <th style="width: 10%;">Section</th>
                                                <th style="width: 15%;">PMS Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $slNo = 1; ?>
                                            @forelse($employees as $employee)
                                                <tr>
                                                    <td>{{ $slNo++ }}</td>
                                                    <td><a href="{{ url('viewprofile', [$employee->Id]) }}" target="_blank"><strong>{{ $employee->Employee }}</strong></a>
                                                        ({{ $employee->Designation }}),
                                                        {{ $employee->Position }}
                                                    </td>
                                                    <td>{{ $employee->Section }}</td>
						    <td class="text-center">
							@if (empty($employee->PMSSubmissionId) && $pmsStatusId != 3 && $pmsStatusId != 2)
                                                            <a href="{{ url('auditemployeepmssubmission', [$employee->Id, $pmsPeriodId]) }}" class="btn btn-xs btn-danger"><i class="fa fa-send"></i> Submit PMS</a> &nbsp;&nbsp;
                                                        @endif
							@if (!empty($employee->PMSSubmissionId))
							    <a href="{{url('viewpmsdetails', [$employee->PMSSubmissionId, 2])}}" class="btn btn-xs btn-primary @if(!(bool)$employee->Status){{"disabled"}}@endif" target="_blank" ><i class="fa fa-eye"></i> View</a>&nbsp;&nbsp;
							@endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td>
                                                        <center>No data to display!</center>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <br/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
