@extends('master')
{{--@section('page-title','Disciplinary Records')--}}
{{--@section('page-header',"Manage Disciplinary Records")--}}
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row"> {{--here--}}
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-sm-12 card" style="padding-top: 10px;padding-bottom: 10px;">
                        <div class="row">
                            <div class="col-md-12 col-lg-12" style="padding-bottom:0;">
                                <div class="row">
                                    <div class="col-md-3 col-sm-9 text-left" style="padding-bottom:0;">
                                        <a href="{{URL::to('disciplinaryinput')}}" class="btn btn-success btn-xs"><i class="fa fa-plus"></i> Add</a>
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
                                {{--<div class="col-sm-12">--}}
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
                                        <label for="Name" class="control-label">Employee Name</label>
                                        <input type="text" autocomplete="off" autocomplete="off" name="Name" value="{{Input::get('Name')}}" id="Name" class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <div class="form-group">
                                        <label for="RecordDate">Action Taken Date </label>
                                        <input type="date" autocomplete="off" name="RecordDate" value="{{Input::get('RecordDate')}}" id="RecordDate" class="form-control"/>
                                    </div>
                                </div>
                                <input type="hidden" value="1" name="Submitted"/>
                                <div class="col-lg-5 col-md-5 col-sm-5 col-8">
                                    <div class="row" style="margin-top:30px;">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <button type="submit" style="" class="btn btn-primary"><i class="fa fa-search"></i> Search</button> &nbsp;
                                            <a href="{{URL::to('disciplinaryindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Clear</a>
                                            <?php $append = ''; ?>
                                            <?php
                                            $serverUri = $_SERVER['REQUEST_URI'];
                                            $replaced = str_replace("/disciplinaryindex",'',$serverUri);
                                            if(strpos($replaced,"export=excel") == false){
                                                if($replaced == ""){
                                                    $append = "";
                                                }else{
                                                    $append = $replaced."&export=excel";
                                                }
                                            }
                                            ?>
                                            &nbsp;<a href="{{Request::url()."$append"}}" class="btn btn-success"><i class="fa fa-file-excel-o"></i> &nbsp;Export to Excel</a>
                                        </div>
                                    </div>
                                </div>
                                {{--</div>--}}
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                <table class="table table-striped table-condensed table-bordered font-small">
                                    <thead>
                                        <tr>
                                            <th>Sl#</th>
                                            <th>Name</th>
                                            <th>CID</th>
                                            <th>Designation</th>
                                            <th>Department</th>
                                            <th>Offence</th>
                                            <th>Action Taken By</th>
                                            <th>Action Taken</th>
                                            <th>Action Taken On</th>
                                            <th class="text-center" style="width:200px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <?php $slNo=Input::has('page')?(Input::get('page')-1)*$perPage+1:1; ?>
                                    @forelse($disciplinaryRecords as $disciplinary)
                                        <tr>
                                            <td>{{$slNo++}}. </td>
                                            <td>{{$disciplinary->Employee}}</td>
                                            <td>{{$disciplinary->CIDNo}}</td>
                                            <td>{{$disciplinary->SavedDesignation}}</td>
                                            <td>{{$disciplinary->Department}}</td>
                                            <td>{{$disciplinary->Record}}</td>
                                            <td>{!! $disciplinary->ActionTakenBy !!}</td>
                                            <td>{{$disciplinary->RecordDescription}}</td>
                                            <td>{{convertDateToClientFormat($disciplinary->RecordDate)}}</td>
                                            <td class="text-center">
                                                <a class="btn btn-xs btn-primary editconfirm" href="{{URL::to('disciplinaryinput',[$disciplinary->Id])}}"><i class="fa fa-edit"></i> Edit</a>&nbsp;&nbsp;<a class="btn btn-danger btn-xs deleteconfirm" href="{{URL::to('disciplinarydelete',[$disciplinary->Id])}}"><i class="fa fa-times"></i> Delete</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center"><strong>No data to display.</strong></td>
                                        </tr>
                                    @endforelse
                                </table>
                                    {{$disciplinaryRecords->appends(Input::except('page'))->links()}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
