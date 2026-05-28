@extends('master')
@section('page-title','Submit PMS For Audit Employees')
@section('page-header','Submit PMS For Audit Employees')
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

                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="no-decoration">{{ $employee[0]->Employee }}, (EmpId: {{ $employee[0]->EmpId }})</h6>
                            </div>
                        </div>
                        <br/>

			{{Form::open(['url' => 'saveauditemployeepmssubmission', 'files' => true])}}
			    {{-- {{Form::hidden('Id', $id)}} --}}
                            <input type="hidden" name="EmployeeId" value="{{ $employeeId }}" />
                            <input type="hidden" name="Employee" value="{{ $employee[0]->Employee }}" />
                            <input type="hidden" name="EmpId" value="{{ $employee[0]->EmpId }}" />
                            <input type="hidden" name="PmsNumberId" value="{{ $pmsPeriodId }}" />
                            <input type="hidden" name="DepartmentId" value="{{ $employee[0]->DepartmentId }}" />
                            <input type="hidden" name="PositionDepartmentRatingId" value="{{ $positionDepartmentRatingId }}" />

                            <input type="hidden" name="WeightageForLevel1" value="{{ $weightageforlevel1 }}" />
                            <input type="hidden" name="WeightageForLevel2" value="{{ $weightageforlevel2 }}" />

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="ExcelApplicant">Goals/Targets Upload file [5MB Max]</label>
                                        <input type="file" accept=".xls,.xlsx,.doc,.docx,.png,.jpg,.gif,.jpeg,.pdf,.ods,.ots,.odt,.ott,.oth,.odm" autocomplete="off" id="ExcelApplicant" name="File" autocomplete="off" class="form-control file-xs"/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="SupportingDoc">Supporting document for Additional Achievement [5MB Max]</label>
                                        <input type="file" accept=".xls,.xlsx,.doc,.docx,.png,.jpg,.gif,.jpeg,.pdf,.ods,.ots,.odt,.ott,.oth,.odm" autocomplete="off" id="SupportingDoc" name="File2" autocomplete="off" class="form-control file-xs"/>
                                    </div>
                                </div>
                            </div>
                            <br/>

                            <div class="table-responsive">
                                <table class="table table-bordered table-condensed">
                                    <thead>
                                        <tr>
                                            <th style="width:3%"></th>
                                            <th class="text-center" style="width:20%">Assesssment Area</th>
                                            <th class="text-center" style="width:10%">Weightage</th>
                                            <th class="text-center" style="width:10%">Self Rating</th>
                                            <th class="text-center" style="width:10%">Level 1 Rating ({{$weightageforlevel1}} %) </th>
                                            @if ($hasLevel2 != 0)
                                                <th class="text-center" style="width:10%">Level 2 Rating ({{$weightageforlevel2}} %)</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $count = 1; $totalweightage = $selfScoreTotal = $level1ScoreTotal = $level2ScoreTotal = 0; ?>
                                        @foreach ($positionDepartmentRatingCriteria as $detail)
                                            <?php $randomKey = randomString(); ?>
                                            <tr>
                                                <td class="text-center">{{ $count++ }}</td>
                                                <input type="hidden" name="pmssubmission[{{ $randomKey }}][ApplicableToLevel2]" value="{{ $detail->ApplicableToLevel2 }}" />
                                                <td class="description">
                                                    <textarea readonly="readonly" style="width:100%;" name="pmssubmission[{{ $randomKey }}][Description]">{!! $detail->Description !!}</textarea>
                                                </td>
                                                <td class="text-center">
                                                    <input readonly="readonly" style="width:100%;" type="number" class="text-right" name="pmssubmission[{{ $randomKey }}][Weightage]" value="{{ $detail->Weightage }}" />
                                                    <?php $totalweightage += doubleval($detail->Weightage); ?>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number"
                                                        @if ($count == 1)
                                                            readonly="readonly" value="{{$goalselfscore}}"
                                                        @endif
                                                    name="pmssubmission[{{$randomKey}}][SelfRating]" onkeydown="return event.keyCode !== 69" min="0" max="{{$detail->Weightage}}" step="any" required="required" class="form-control input-xs self-rating-score figure" />
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" name="pmssubmission[{{$randomKey}}][Level1Rating]" onkeydown="return event.keyCode !== 69" min="0" max="{{$detail->Weightage}}" step="any" required="required" class="form-control input-xs level1-rating-score figure" />
                                                </td>
                                                @if ($hasLevel2 != 0)
                                                    <td class="description">
                                                        @if ($detail->ApplicableToLevel2 != 0)
                                                            <input type="number" onkeydown="return event.keyCode !== 69" name="pmssubmission[{{$randomKey}}][Level2Rating]" min="0" max="{{$detail->Weightage}}" step="any" required="required" class="form-control input-xs level2-rating-score figure" />
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach

                                        <tr>
                                            <td></td>
                                            <td class="text-right"><strong>Total Score</strong></td>
                                            <td class="text-right">{{ number_format($totalweightage, 2) }}</td>
                                            <td>
                                                <input type="text" value="{{ number_format($selfScoreTotal, 2) }}" autocomplete="off" class="form-control input-xs self-rating-total text-right" disabled="disabled" />
                                            </td>
                                            <td>
                                                <input type="text" value="{{ number_format($level1ScoreTotal, 2) }}" autocomplete="off" class="form-control input-xs level1-rating-total text-right" disabled="disabled" />
                                            </td>
                                            @if ($hasLevel2 != 0)
                                                <td>
                                                    <input type="text" value="{{ number_format($level2ScoreTotal, 2) }}" autocomplete="off" class="form-control input-xs level2-rating-total text-right" disabled="disabled" />
                                                </td>
                                            @endif
                                        </tr>

                                        <input type="hidden" name="TotalLevel1Score" id="total-level-1-score" value="{{ number_format($level1ScoreTotal, 2) }}" />
                                        <input type="hidden" name="TotalLevel2Score" id="total-level-2-score" value="{{ number_format($level2ScoreTotal, 2) }}" />
                                    </tbody>
                                </table>
                            </div>

                            <br/>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="Remarks">Remarks</label>
                                        <textarea style="width:100%;" name="Remarks"></textarea>
                                    </div>
                                </div>
                            </div>

                            <br/>
                            <button type="submit" class="btn btn-success"><i class="fa fa-send"></i> Submit</button>&nbsp;&nbsp;
                            <a href="{{URL::to('auditemployeeindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>&nbsp;&nbsp;
			{{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
