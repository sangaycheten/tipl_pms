@extends('master')
@section('page-title',$update?'Update Promotion Criteria':'Set Promotion Criteria')
@section('page-header',$update?'Update Promotion Criteria':'Set Promotion Criteria')
@section('action-button')
    @parent
    <a href="{{URL::to('promotioncriteriaindex')}}" class="btn btn-success btn-xs viewall-confirm"><i class="fa fa-list"></i> View All</a>
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
                        
                        {{Form::open(['url'=>'savepromotioncriteria'])}}
                            @if ($update == true)
                                <input type="hidden" name="Id" value={{($promotioncriteria['Id']??old('Id'))}} />
                                <div class="row">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="FromGradeStepId" class="control-label">GradeStep (From) <span class="required">*</span></label></label>
                                            <select name="FromGradeStepId" class="form-control select2" id="FromGradeStepId">
                                                <option value=""> -- SELECT ONE -- </option>
                                                @foreach($gradestep as $grades)
                                                    <option @if($grades->Id == ($promotioncriteria['FromGradeStepId']??old('FromGradeStepId')))selected="selected"@endif value="{{$grades->Id}}">{{$grades->Name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="ToGradeStepId" class="control-label">GradeStep (To) <span class="required">*</span></label></label>
                                            <select name="ToGradeStepId" class="form-control select2" id="ToGradeStepId">
                                                <option value=""> -- SELECT ONE -- </option>
                                                @foreach($gradestep as $grades)
                                                    <option @if($grades->Id == ($promotioncriteria['ToGradeStepId']??old('ToGradeStepId')))selected="selected"@endif value="{{$grades->Id}}">{{$grades->Name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="OutstandingCount" class="control-label">Outstanding (Count) <span class="required">*</span></label>
                                            <input type="number" step="1" id="OutstandingCount" name="OutstandingCount" value="{{isset($promotioncriteria['OutstandingCount'])?$promotioncriteria['OutstandingCount']:old('OutstandingCount')}}" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="OutstandingAndGoodCount" class="control-label">Outstanding and Good (Count) <span class="required">*</span></label>
                                            <input type="number" step="1" id="OutstandingAndGoodCount" name="OutstandingAndGoodCount" value="{{isset($promotioncriteria['OutstandingAndGoodCount'])?$promotioncriteria['OutstandingAndGoodCount']:old('OutstandingAndGoodCount')}}" class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="RegularPromotionCount" class="control-label">Regular Promotion (Count) <span class="required">*</span></label>
                                            <input type="number" step="1" id="RegularPromotionCount" name="RegularPromotionCount" value="{{isset($promotioncriteria['RegularPromotionCount'])?$promotioncriteria['RegularPromotionCount']:old('RegularPromotionCount')}}" class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="FromGradeStepId" class="control-label">GradeStep (From) <span class="required">*</span></label></label>
                                            <select name="FromGradeStepId" class="form-control select2" id="FromGradeStepId">
                                                <option value=""> -- SELECT ONE -- </option>
                                                @foreach($gradestep as $grades)
                                                    <option value="{{$grades->Id}}">{{$grades->Name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="ToGradeStepId" class="control-label">GradeStep (To) <span class="required">*</span></label></label>
                                            <select name="ToGradeStepId" class="form-control select2" id="ToGradeStepId">
                                                <option value=""> -- SELECT ONE -- </option>
                                                @foreach($gradestep as $grades)
                                                    <option value="{{$grades->Id}}">{{$grades->Name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="OutstandingCount" class="control-label">Outstanding (Count) <span class="required">*</span></label>
                                            <input type="number" step="1" id="OutstandingCount" name="OutstandingCount" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="OutstandingAndGoodCount" class="control-label">Outstanding and Good (Count) <span class="required">*</span></label>
                                            <input type="number" step="1" id="OutstandingAndGoodCount" name="OutstandingAndGoodCount" class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="RegularPromotionCount" class="control-label">Regular Promotion (Count) <span class="required">*</span></label>
                                            <input type="number" step="1" id="RegularPromotionCount" name="RegularPromotionCount" class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row" >
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <button type="submit" style="" class="btn btn-primary"><i class="fa fa-send"></i> {{$update?'Update':'Save'}}</button> &nbsp;&nbsp;
                                    <a href="{{URL::to('promotioncriteriaindex')}}" style="" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>&nbsp;&nbsp;
                                </div>
                            </div>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
