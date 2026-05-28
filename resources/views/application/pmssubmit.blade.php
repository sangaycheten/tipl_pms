@extends('master')
@section('page-title','Resubmit PMS')
@section('page-header','Resubmit PMS')
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
                        {{Form::open(['url'=>'resubmitpms','files'=>true])}}
                        {{Form::hidden('Id',$id)}}
                            @if(Auth::user()->DepartmentId != 7)
                                @if((bool)$filePath)
                                    <div class="row">
                                        <div class="col-md-4">
                                            <a href="{{url('filedownload')}}?file={{$filePath}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download Self Rated File</a>
                                            <br>
                                            <br>
                                        </div>
                                    </div>
                                @endif
                            @endif
                            @if((bool)$filePath3)
                            <div class="row">
                                <div class="col-md-4">
                                    <a href="{{url('filedownload')}}?file={{$filePath3}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download Level 1 Rated File</a>
                                    <br>
                                    <br>
                                </div>
                            </div>
                            @endif
                            @if((bool)$filePath4)
                            <div class="row">
                                <div class="col-md-4">
                                    <a href="{{url('filedownload')}}?file={{$filePath4}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download Level 2 Rated File</a>
                                    <br>
                                </div>
                            </div>
                            @endif
                            @if((bool)$filePath2)
                                <div class="row">
                                    <div class="col-md-4">
                                        <a href="{{url('filedownload')}}?file={{$filePath2}}" target="_blank" class="btn btn-xs btn-inverse-danger"><i class="fa fa-download"></i> Download Supporting Document</a>
                                        <br>
                                    </div>
                                </div>
                            @endif
                        <div class="row">
                            @if(Auth::user()->DepartmentId != 7)
                                @if((bool)$filePath)
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="Position">Upload Goals/Targets again [5MB Max]</label>
                                            <input type="file" accept=".xls,.xlsx,.doc,.docx,.png,.jpg,.gif,.jpeg,.pdf,.ods,.ots,.odt,.ott,.oth,.odm" autocomplete="off" id="ExcelApplicant" name="File" autocomplete="off" class="form-control file-xs"/>
                                        </div>
                                    </div>
                                @else
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="ExcelApplicant">Goals/Targets Upload file [5MB Max]</label>
                                            <input type="file" accept=".xls,.xlsx,.doc,.docx,.png,.jpg,.gif,.jpeg,.pdf,.ods,.ots,.odt,.ott,.oth,.odm" autocomplete="off" id="ExcelApplicant" name="File" autocomplete="off" class="form-control file-xs"/>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            <div class="col-md-6 @if(Auth::user()->DepartmentId != 7){{"offset-2"}}@endif">
                                <div class="form-group">
                                    <label for="SupportingDoc">Upload Supporting document for Additional Achievement @if((bool)$filePath2){{"again"}}@endif [5MB Max]</label>
                                    <input type="file" accept=".xls,.xlsx,.doc,.docx,.png,.jpg,.gif,.jpeg,.pdf,.ods,.ots,.odt,.ott,.oth,.odm" autocomplete="off" id="SupportingDoc" name="File2" autocomplete="off" class="form-control file-xs"/>
                                </div>
                            </div>
                        </div>
                            @if(!empty($scoresOfSubordinate))
                                <div class="table-responsive">
                                    <table class="table table-bordered table-condensed">
                                        <thead>
                                        <tr>
                                            <th style="width:20px;">Sl #</th>
                                            <th>Section</th>
                                            <th>Assessment Area</th>
                                            <th style="width:20px;">Avg. Score</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $total = 0; $slNo = 1; $adjusted = 0; ?>
                                        @foreach($scoresOfSubordinate as $scoreOfSubordinate)

                                            <tr data-total="{{$scoreOfSubordinate->SectionEmployeeCount}}">
                                                <td>{{$slNo++}}</td>
                                                <td>{{$scoreOfSubordinate->Section}}</td>
                                                <td>Goal Achievement</td>
                                                <td>{{$scoreOfSubordinate->Score}}<?php $total+=doubleval($scoreOfSubordinate->SectionEmployeeCount); $adjusted+=doubleval($scoreOfSubordinate->Score)*doubleval($scoreOfSubordinate->SectionEmployeeCount); ?></td>
                                            </tr>
                                        @endforeach
                                        <?php $average = $adjusted/$total; ?>
                                        <tr>
                                            <td colspan="3" style="text-align:right;font-weight:bold;">Average Weighted Score</td>
                                            <td><strong>{{round($average,2)}}</strong></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed" id="calc-total">
                                <thead>
                                <tr>
                                    <th style="width:40px;">Sl #</th>
                                    <th>Assessment Area</th>
                                    <th class="text-center">Weight (%)</th>
                                    <th class="text-center" style="width:16%;">Self Rating</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $count=1; $sum = 0; ?>
                                @foreach($details as $assessmentArea)
                                    <?php $randomKey = randomString(); ?>
                                    <tr>
                                        <td class="text-center">{{$count}}.</td>
                                        <td class="description">
                                            <input type="hidden" name="pmssubmissiondetail[{{$randomKey}}][Id]" value="{{$assessmentArea->Id}}"/>
                                            {{$assessmentArea->AssessmentArea}}
                                        </td>
                                        <td class="text-center">
                                            {{$assessmentArea->Weightage}}
                                        </td>
                                        <td>
                                            <input type="number" onkeydown="return event.keyCode !== 69" name="pmssubmissiondetail[{{$randomKey}}][SelfRating]"
                                                   @if($count == 1 && Auth::user()->DepartmentId == 7 && Auth::user()->PositionId != CONST_POSITION_HOD && Auth::user()->PositionId != CONST_POSITION_HOS)
                                                   value="{{$goalAchievementScore}}"
                                                        <?php $sum+=doubleval($goalAchievementScore);?>
                                                   @elseif($count == 1 && Auth::user()->DepartmentId == 7 && Auth::user()->PositionId == CONST_POSITION_HOS)
                                                        <?php $sum+=doubleval((($subordinateScorePercentage/100 * $average) + ((100-$subordinateScorePercentage)/100 * $goalAchievementScore)));?>
                                                        readonly="readonly" data-goal="{{$goalAchievementScore}}" data-sub="{{$average}}" data-subper="{{$subordinateScorePercentage/100}}" value="{{round((($subordinateScorePercentage/100 * $average) + ((100-$subordinateScorePercentage)/100 * $goalAchievementScore)),2)}}"
                                                   @else
                                                   value="{{$assessmentArea->SelfRating}}"
                                                        <?php $sum+=doubleval($assessmentArea->SelfRating);?>
                                                   @endif
                                                    min="0" max="{{$assessmentArea->Weightage}}"
                                                   step="any" required="required" class="form-control
                                                   @if(Auth::user()->PositionId <> CONST_POSITION_HOD && $count == 1 && Auth::user()->DepartmentId == 7)
                                                        {{"text-center"}}
                                                   @endif input-xs figure"/>

                                        </td>
                                    </tr>
                                    <?php $count++; ?>
                                @endforeach
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Total</strong></td>
                                    <td>
                                        <input type="text" autocomplete="off" value="{{number_format($sum,2)}}" class="form-control input-xs" id="figure-total" disabled="disabled"/>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="well">
                            <h6>History of Evaluation</h6>
                            {!! $assessmentArea->History !!}
                        </div>
                        <button type="button" id="save-appraisee" class="btn btn-primary">Save as Draft</button>
                        <button type="submit" class="btn btn-success">Submit</button>
                        <a href="{{URL::to('trackpms')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
