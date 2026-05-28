@extends('master')
@section('page-title', 'L1 Appraisal — Subordinates')

@section('pagestyles')
<style>
    .appraise-table th { background:#f4f6f9; color:#555; font-size:0.82rem; font-weight:600; }
    .appraise-table td { vertical-align:middle; font-size:0.85rem; color:#333; }
    .appraise-table tbody tr:hover { background:#f9fbff; }
</style>
@endsection

@section('content')
<div class="container-fluid">

    @if(session('successmessage'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle mr-1"></i> {{ session('successmessage') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if($errors->has('error'))
        <div class="alert alert-danger">{{ $errors->first('error') }}</div>
    @endif

    <div class="col-sm-12 card" style="padding:0; background:#fff; color:#333;">
        <div style="padding:14px 20px; border-bottom:1px solid #e8ecf0;">
            <strong style="font-size:0.97rem; color:#333;">Subordinates Awaiting Your Appraisal</strong>
        </div>

        @if($rows->isEmpty())
            <div style="padding:32px; text-align:center; color:#888; font-size:0.9rem;">
                <i class="fa fa-info-circle mr-1"></i>
                No subordinates have submitted their self-rating yet.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm mb-0 appraise-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Employee</th>
                            <th>Designation</th>
                            <th>Department</th>
                            <th style="width:70px;">Cycle</th>
                            <th style="width:160px;">Your Status</th>
                            <th style="width:110px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $row->employee->Name ?? '—' }}</strong>
                                    @if($row->employee)
                                        <br><small style="color:#999;">{{ $row->employee->EmpId }}</small>
                                    @endif
                                </td>
                                <td>{{ $row->employee->Designation ?? '—' }}</td>
                                <td>{{ $row->employee->Department ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-primary" style="font-size:0.82rem;">{{ $row->cycle }}</span>
                                </td>
                                <td>
                                    @if($row->isSubmitted)
                                        <span class="badge badge-success" style="font-size:0.8rem;">
                                            <i class="fa fa-check mr-1"></i> Submitted
                                        </span>
                                        <br><small style="color:#999;">{{ \Carbon\Carbon::parse($row->submittedAt)->format('d M Y, H:i') }}</small>
                                    @elseif($row->isDraft)
                                        <span class="badge badge-warning" style="color:#333; font-size:0.8rem;">
                                            <i class="fa fa-pencil mr-1"></i> Draft Saved
                                        </span>
                                    @else
                                        <span class="badge badge-secondary" style="font-size:0.8rem;">
                                            <i class="fa fa-clock-o mr-1"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($row->isSubmitted)
                                        <a href="{{ route('goals.appraise.show', [$row->employeeId, strtolower($row->cycle)]) }}"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="fa fa-eye mr-1"></i> View
                                        </a>
                                    @else
                                        <a href="{{ route('goals.appraise.show', [$row->employeeId, strtolower($row->cycle)]) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fa fa-star mr-1"></i> Appraise
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
