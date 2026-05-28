@extends('master')
@section('page-title','Performance Goals')
@section('page-header','Performance Goals')
@section('pagestyles')
    <style>
        .custom-file-swm {
            color: transparent;
            width:100%;
        }
        .custom-file-swm::-webkit-file-upload-button {
            visibility: hidden;
        }
        .custom-file-swm::before {
            content: 'Select file';
            color: black;
            display: inline-block;
            background: -webkit-linear-gradient(top, #f9f9f9, #e3e3e3);
            border: 1px solid #999;
            border-radius: 3px;
            padding: 3px 4px;
            outline: none;
            white-space: nowrap;
            -webkit-user-select: none;
            cursor: pointer;
            text-shadow: 1px 1px #fff;
            font-weight: 700;
            font-size: 10pt;
        }
        .custom-file-swm:hover::before {
            border-color: black;
        }
        .custom-file-swm:active {
            outline: 0;
        }
        .custom-file-swm:active::before {
            background: -webkit-linear-gradient(top, #e3e3e3, #f9f9f9);
        }
    </style>
    {{Html::style("assets/plugins/lightcase/css/lightcase.css")}}
@endsection
@section('pagescripts')
    {{Html::script("assets/plugins/lightcase/js/lightcase.js")}}
@endsection
@section('content')
    <div class="row m-b-30 dashboard-header">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-6">
                    <div class="col-sm-12 card" style="padding-top: 16px;padding-bottom: 18px;">
                        <div class="row">
                            <div class="col-md-12">
                                <h6>Employee Profile</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <table style="width:100%;" class="simple-table">
                                    @foreach($details as $userDetail)
                                        <tr>
                                            <th colspan="2" class="text-center">
                                                @if((bool)$userDetail->ProfilePicPath)
                                                    <a href="{{asset((bool)$userDetail->ProfilePicPath?$userDetail->ProfilePicPath:'images/avatar.png')}}" data-rel="lightcase">
                                                        @endif
                                                        <img class="rounded-circle img-thumbnail" src="{{asset((bool)$userDetail->ProfilePicPath?$userDetail->ProfilePicPath:'images/avatar.png')}}" style="height:117px; "/>
                                                        @if((bool)$userDetail->ProfilePicPath)
                                                    </a>
                                                @endif
                                                <br><br>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Name <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->Name}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Date of Birth  <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{convertDateToClientFormat($userDetail->DateOfBirth)}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Mobile <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->MobileNo}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Extension <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->Extension}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Emp Id <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->EmpId}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">CID <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->CIDNo}}</td>
                                        </tr>
                                        
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
		</div>

                <div class="col-lg-6">
                    <div class="col-sm-12 card" style="padding-top: 16px;padding-bottom: 18px;">
                        <div class="row">
                            <div class="col-md-12">
                                <h6>Employment Details</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <table style="width:100%;" class="simple-table">
                                    @foreach($details as $userDetail)
                                        <tr>
                                            <th style="width:35%;">Date of Appointment  <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{convertDateToClientFormat($userDetail->DateOfAppointment)}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Employment Type<span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> Regular</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Designation <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->DesignationLocation}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Grade/Step<span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->GradeStep}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Current Pay Scale<span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->PayScale}}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Appraiser (Level 1) <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {!! $userDetail->Level1Name or 'N/A' !!}</td>
                                        </tr>
                                        <tr>
                                            <th style="width:35%;">Appraiser (Level 2) <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {!! $userDetail->Level2Name or 'N/A' !!}</td>
                                        </tr>
                                        {{--<tr>--}}
                                        {{--<th style="width:35%;">Section <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>--}}
                                        {{--<td style="padding-left:25px;"> {{$userDetail->Section}}</td>--}}
                                        {{--</tr>--}}
                                        {{--<tr>--}}
                                        {{--<th style="width:35%;">Department <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>--}}
                                        {{--<td style="padding-left:25px;"> {{$userDetail->Department}}</td>--}}
                                        {{--</tr>--}}
                                    @endforeach
                                </table>
                            </div>
                        </div>
		            </div>

                    <div class="col-sm-12 card" style="padding-top: 16px;padding-bottom: 18px;">
                        <div class="row">
                            <div class="col-md-12">
                                <h6>Employment Status</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <table style="width:100%;" class="simple-table">
                                    <?php $statusArray = ['3'=>'Probation','4'=>'Suspended','5'=>'Terminated','1'=>'Regular','2'=>'In-active (EOL, Study Leave, etc)','0'=>'Resigned']; ?>
                                    @foreach($details as $userDetail)
                                        <tr>
                                            <th style="width:35%;">Status <span class="pull-right d-none d-sm-block">:</span> &nbsp;&nbsp;</th>
                                            <td style="padding-left:25px;"> {{$userDetail->GradeId==CONST_GRADE_E0?'Contract':$statusArray[$userDetail->Status]}}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12 col-12">
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

                        <!-- include goal details -->
                        @include('automation.includes.goaltaskindex')
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
