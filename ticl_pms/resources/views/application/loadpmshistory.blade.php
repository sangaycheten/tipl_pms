    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-12 col-lg-12" style="padding-bottom:0;">
                                <div class="row">
                                    <div class="col-md-3 col-sm-9 text-left" style="padding-bottom:0;">
{{--                                        <a href="{{URL::to('sectioninput')}}" class="btn btn-success btn-xs"><i class="fa fa-plus"></i> Add</a>--}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-condensed table-bordered less-padding">
                                        <thead>
                                        <tr>
                                            <th width="20">Sl#</th>
                                            <th>PMS Period</th>
                                            <th>Score</th>
                                            <th>Result</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $slNo = 1; $labelArray = []; $scoreArray = []; $historyCount = count($history); $rowCount = 1;?>
                                        @forelse($history as $data)
                                            <?php
                                                $month = date_format(date_create($data->StartDate),'m');
                                                $year = date_format(date_create($data->StartDate),'Y');
                                                if($month == '01'){
                                                    $period = "1st July, ".($year - 1)." - 31st December, ".($year - 1);
                                                }else{
                                                    $period = "1st Jan, ".$year." - 30th June, ".$year;
                                                }
                                            ?>
                                            <tr>
                                                <td>{{$slNo++}}</td>
                                                <td>PMS {{$data->PMSNumber}};  {{$period}}<?php array_push($labelArray,'PMS '.$data->PMSNumber); ?></td>
                                                <td>
                                                    {{$data->PMSScore}}<?php array_push($scoreArray,$data->PMSScore); ?>
                                                </td>
                                                <td>
                                                    @if($rowCount == $historyCount)
                                                        @if((bool)$data->OfficeOrderPath)
                                                            @if($data->OfficeOrderEmailed == 1)
                                                                {{$data->PMSResult}} &nbsp; @if((bool)$data->PMSRemarks)<br/>{{$data->PMSRemarks}}@endif
                                                            @else
                                                                {{--No Action--}}
                                                                {{$data->PMSResult ? $data->PMSResult : "No Action"}}
                                                            @endif
                                                        @else
                                                            {{--No Action--}}
                                                            {{$data->PMSResult ? $data->PMSResult : "No Action"}}
                                                        @endif
                                                    @else
                                                        {{$data->PMSResult ? $data->PMSResult : "No Action"}} &nbsp; @if((bool)$data->PMSRemarks)<br/>{{$data->PMSRemarks}}@endif
                                                    @endif
                                                </td>
                                            </tr>
                                            <?php $rowCount++; ?>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">No data to display!</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php $totalPMSPeriods = count($history); $totalRows = 1; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

