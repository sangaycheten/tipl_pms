@extends('master')
@section('page-title','Promotion')
@section('page-header',"Manage Promotion Criteria")
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-12 col-lg-12" style="padding-bottom:0;">
                                <br/>
                                <div class="row">
                                    <div class="col-md-3 col-sm-9 text-left" style="padding-bottom:0;">
                                        <a href="{{URL::to('promotioncriteriainput')}}" class="btn btn-success btn-xs"><i class="fa fa-plus"></i> Add </a>
                                        <br><br>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br/>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-striped table-condensed table-bordered font-small">
                                        <thead>
                                            <tr>
                                                <th>Sl#</th>
                                                <th>GradeStep (From)</th>
                                                <th>GradeStep (To)</th>
                                                <th>Outstanding (Count)</th>
                                                <th>Outstanding and Good (Count)</th>
                                                <th>Regular Promotion (Count)</th>
                                                <th class="text-center" style="width:20%;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $count = 1; ?>
					    @forelse($promotioncriteria as $criteria)
						<?php
                                                    $gradestep = DB::select("SELECT A.Status FROM mas_gradestep A WHERE A.Id = ? ", [$criteria->FromGradeStepId]);
                                                    $gradeStepStatus = (int) $gradestep[0]->Status;
						?>
						@if ($gradeStepStatus == 1)
                                                    <tr>
                                                       <td>{{ $count++ }}. </td>
                                                       <td>{{ $criteria->FromGradeStepName }}</td>
                                                       <td>{{ $criteria->ToGradeStepName }}</td>
                                                       <td>{{ $criteria->OutstandingCount }}</td>
                                                       <td>{{ $criteria->OutstandingAndGoodCount }}</td>
                                                       <td>{{ $criteria->RegularPromotionCount }}</td>
                                                       <td class="text-center">
                                                           <a class="btn btn-primary btn-xs editconfirm" href="{{URL::to('promotioncriteriainput',[$criteria->Id])}}"><i class="fa fa-edit"></i> Edit</a>&nbsp;&nbsp;
                                                           {{-- <a class="btn btn-danger btn-xs deleteconfirm" href="{{URL::to('promotioncriteriadelete',[$criteria->Id])}}"><i class="fa fa-times"></i> Delete</a>&nbsp;&nbsp; --}}
                                                       </td>
						   </tr>
						@endif
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center"><strong>No data to display.</strong></td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
