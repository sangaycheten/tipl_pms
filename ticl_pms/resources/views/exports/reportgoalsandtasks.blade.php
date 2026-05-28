<div class="row">
    <div class="col-md-12">
        <br />
        <div class="row">
            <div class="col-md-12">
                <h6 class="no-decoration">Goals & Target of <i>{{$empName}}</i></h6>
            </div>
        </div>
        <div class="table-responsive">
            <table id="table-to-clone"
                class="sticky-columns table table-bordered table-striped table-hover table-condensed">
                <thead>
                    <tr>
                        <th>Sl #</th>
                        <th>Goal Description</th>
                        <th>Weightage</th>
                        <th>Target</th>
                        <th>SelfScore</th>
                        <th>SupervisorScore</th>
                        <th>Attachment</th>
                        <th>Achievement</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $slNo=Input::has('page')?(Input::get('page')-1)*$perPage+1:1; ?>
                    @forelse($goaldetails as $data)
                    <tr>
                        <td>{{$slNo++}}</td>
                        <td>{{$data->GoalDescription}}</td>
                        <td>{{$data->GoalWeightage}}</td>
                        <td>{{$data->GoalTarget}}</td>
                        <td>{{$data->SelfScore}}</td>
                        <td>{{$data->SupervisorScore}}</td>
                        <td>{{$data->FilePath}}</td>
                        <td>{{$data->Achievement}}</td>
                        <td>{{$data->GoalStatus}}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center">No data to display!</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            {{$goaldetails->appends(Input::except('page'))->links()}}
        </div>
    </div>
    <br />
    <div class="col-md-12">
        <br />
        <div class="row">
            <div class="col-md-12">
                <h6 class="no-decoration">Tasks & Target of <i>{{$empName}}</i></h6>
            </div>
        </div>
        <div class="table-responsive">
            <table id="table-to-clone"
                class="sticky-columns table table-bordered table-striped table-hover table-condensed">
                <thead>
                    <tr>
                        <th>Sl #</th>
                        <th>Task Description</th>
                        <th>Weightage</th>
                        <th>Target</th>
                        <th>SelfScore</th>
                        <th>SupervisorScore</th>
                        <th>Attachment</th>
                        <th>Achievement</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $slNo=Input::has('page')?(Input::get('page')-1)*$perPage+1:1; ?>
                    @forelse($taskdetails as $data)
                    <tr>
                        <td>{{$slNo++}}</td>
                        <td>{{$data->TaskDescription}}</td>
                        <td>{{$data->TaskWeightage}}</td>
                        <td>{{$data->TaskTarget}}</td>
                        <td>{{$data->SelfScore}}</td>
                        <td>{{$data->SupervisorScore}}</td>
                        <td>{{$data->FilePath}}</td>
                        <td>{{$data->Achievement}}</td>
                        <td>{{$data->GoalStatus}}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center">No data to display!</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            {{$taskdetails->appends(Input::except('page'))->links()}}
        </div>
    </div>
</div>
