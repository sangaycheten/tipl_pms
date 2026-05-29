@extends('master')
@section('page-title', 'Set Common Goals')
@section('page-header', 'Set Common Goals')

@section('pagestyles')
<style>
    .cg-toolbar {
        background: #226b86;
        border-radius: 6px;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 0;
    }
    .cg-toolbar .year-group {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #fff;
        font-weight: 500;
    }
    .cg-toolbar .year-group select {
        min-width: 90px;
        border-radius: 4px;
        border: 1px solid #ccc;
        padding: 4px 8px;
        font-size: 0.93rem;
        color: #333;
    }
    .goal-card { border:1px solid #d0dce4; border-radius:6px; margin-bottom:16px; overflow:hidden; }
    .goal-card-header {
        background: #226b86; color: #fff; padding: 10px 16px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .badge-status {
        font-size: 0.75rem; padding: 3px 10px; border-radius: 30px;
        border: 1px solid #fff; font-weight: 600; background: transparent;
    }
    .goal-card-body table { width:100%; border-collapse:collapse; font-size:0.88rem; }
    .goal-card-body thead tr { background: rgba(0,0,0,0.18); }
    .goal-card-body th { padding:8px 12px; border-bottom:1px solid rgba(255,255,255,0.2); color:#fff; font-weight:600; }
    .goal-card-body td { padding:7px 12px; border-bottom:1px solid rgba(255,255,255,0.12); vertical-align:top; color:#fff; }
    .goal-card-body tr:last-child td { border-bottom:none; }
    .goal-card-footer {
        background:#f7fafc; padding:8px 14px; display:flex; gap:8px;
        border-top:1px solid #dde6ec; justify-content:flex-end;
    }
    .empty-state { text-align:center; padding:60px 20px; color:#7a9db5; }
    .empty-state i { font-size:3rem; margin-bottom:14px; display:block; }
</style>
@endsection

@section('content')
<div class="row">
<div class="col-sm-12">
<div class="col-sm-12 card" style="padding: 16px 18px;">

    @if(Session::has('successmessage'))
    <div class="alert alert-success mb-3">{{ Session::get('successmessage') }}</div>
    @endif
    @if(Session::has('infomessage'))
    <div class="alert alert-info mb-3">{{ Session::get('infomessage') }}</div>
    @endif

    {{-- ═══ TOOLBAR ═══ --}}
    <div class="cg-toolbar">
        <a href="{{ route('commongoal.create', ['year' => $selectedYear]) }}"
           class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> ADD NEW GOAL
        </a>
        <a href="{{ route('commongoal.import', ['year' => $selectedYear]) }}"
           class="btn btn-default btn-sm" style="border:1px solid #ccc;background:#fff;">
            <i class="fa fa-download"></i> IMPORT TEMPLATE
        </a>

        <div class="year-group">
            <span>Year</span>
            <form method="GET" action="{{ route('commongoal.index') }}" id="yearForm" style="margin:0;">
                <select name="year" onchange="document.getElementById('yearForm').submit()">
                    @for($y = date('Y') + 1; $y >= 2020; $y--)
                    <option value="{{ $y }}" @if($y == $selectedYear) selected @endif>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </div>

    {{-- ═══ GOAL SETS LIST ═══ --}}
    <div class="mt-4">
        @forelse($goals as $goal)
        <div class="goal-card">
            {{-- Header --}}
            <div class="goal-card-header">
                <span>
                    <strong>{{ $goal->Title ?: 'Common Goals ' . $goal->Year }}</strong>
                    &nbsp;&nbsp;
                    <span style="font-size:0.82rem; opacity:0.85;">
                        {{ $goal->goalCount }} goal(s) &mdash; {{ $goal->Year }}
                    </span>
                </span>
                <span style="display:flex;align-items:center;gap:10px;">
                    <span style="font-size:0.8rem;opacity:0.9;">
                        <i class="fa fa-users"></i>
                        {{ $goal->assignments->count() }} assigned
                    </span>
                    <span class="badge-status">{{ strtoupper($goal->Status) }}</span>
                </span>
            </div>

            {{-- Body: goal summary table --}}
            <div class="goal-card-body">
                @if($goal->goalDetails->isNotEmpty())
                <table>
                    <thead>
                        <tr>
                            <th style="width:70px; text-align:center;">Goal No.</th>
                            <th>Description</th>
                            <th style="width:110px;">Period</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($goal->goalDetails as $detail)
                        @php $goalNum = (int)($detail->DisplayOrder / 1000); @endphp
                        <tr>
                            <td class="text-center">{{ chr(64 + $goalNum) }}</td>
                            <td>{{ $detail->Description }}</td>
                            <td>
                                @if($detail->InH1 && $detail->InH2) H1 &amp; H2
                                @elseif($detail->InH1) H1
                                @elseif($detail->InH2) H2
                                @else Full Year
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="text-muted" style="padding:12px 16px; margin:0; font-size:0.85rem; font-style:italic;">
                    No goals defined yet.
                </p>
                @endif
            </div>

            {{-- Footer Actions --}}
            <div class="goal-card-footer">
                <a href="{{ route('commongoal.show', $goal->Id) }}"
                   class="btn btn-sm btn-info">
                    <i class="fa fa-eye"></i> View
                </a>
                @if(!$goal->isLocked)
                <a href="{{ route('commongoal.edit', $goal->Id) }}"
                   class="btn btn-sm btn-warning">
                    <i class="fa fa-pencil"></i> Edit
                </a>
                @if($goal->Status !== 'published')
                <form method="POST"
                      action="{{ route('commongoal.publish', $goal->Id) }}"
                      style="display:inline;">
                    @csrf
                    <button type="button" class="btn btn-sm btn-success cg-confirm-btn"
                            data-confirm-title="Publish Common Goals"
                            data-confirm-msg="Publish these common goals to all assigned employees? Their supervisors will receive them to set individual weightages."
                            data-confirm-type="publish">
                        <i class="fa fa-check"></i> Publish
                    </button>
                </form>
                @endif
                <form method="POST"
                      action="{{ route('commongoal.destroy', $goal->Id) }}"
                      style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-sm btn-danger cg-confirm-btn"
                            data-confirm-title="Delete Common Goal Set"
                            data-confirm-msg="Are you sure you want to delete this common goal set? This action cannot be undone."
                            data-confirm-type="danger">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </form>
                @else
                <span class="btn btn-sm btn-default disabled" style="opacity:0.55; cursor:default;"
                      title="Locked — one or more employees have started self-rating">
                    <i class="fa fa-lock"></i> Locked
                </span>
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i class="fa fa-list-alt"></i>
            <p>No common goals set for <strong>{{ $selectedYear }}</strong>.</p>
            <a href="{{ route('commongoal.create', ['year' => $selectedYear]) }}"
               class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Add New Goal
            </a>
        </div>
        @endforelse
    </div>

</div>
</div>
</div>
@endsection

@section('pagescripts')
<script>
$(document).on('click', '.cg-confirm-btn', function (e) {
    e.preventDefault();
    var $btn  = $(this);
    var $form = $btn.closest('form');
    var title = $btn.data('confirm-title') || 'Confirm';
    var msg   = $btn.data('confirm-msg')   || 'Are you sure?';
    var type  = $btn.data('confirm-type')  || 'default';

    var yesLabel  = type === 'danger'  ? 'Yes, Delete' : 'Confirm';
    var yesBtnCls = type === 'danger'  ? 'btn-danger'  : 'btn-success';
    var icon      = type === 'danger'  ? 'fa fa-trash' : 'fa fa-check';

    $.confirm({
        title   : title,
        content : '<p style="margin:0; color:#fff; font-size:0.95rem;">' + msg + '</p>',
        buttons : {
            confirm : {
                text     : '<i class="' + icon + '"></i> ' + yesLabel,
                btnClass : yesBtnCls,
                action   : function () { $form[0].submit(); }
            },
            cancel : {
                text     : 'Cancel',
                btnClass : 'btn-default',
                action   : function () {}
            }
        }
    });
});
</script>
@endsection
