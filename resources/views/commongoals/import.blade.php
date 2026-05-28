@extends('master')
@section('page-title', 'Import Common Goals Template')
@section('page-header', 'Import Common Goals Template')

@section('pagestyles')
<style>
    .import-card { background:#fff; border-radius:6px; box-shadow:0 1px 4px rgba(0,0,0,0.1); padding:28px 32px; max-width:640px; margin:0 auto; }
    .template-steps { background:#f5f7fa; border-radius:6px; padding:16px 20px; margin-bottom:20px; font-size:0.88rem; color:#444; }
    .template-steps ol { margin:0; padding-left:18px; }
    .template-steps li { margin-bottom:4px; }
</style>
@endsection

@section('content')
<div class="row">
<div class="col-sm-12">
<div class="col-sm-12 card" style="padding:16px 18px;">

    <div class="import-card">
        <h5 class="mb-3" style="color:#226b86;">
            <i class="fa fa-download mr-2"></i>Import Common Goals Template
        </h5>

        <div class="template-steps">
            <strong>Steps:</strong>
            <ol>
                <li>Download the template file below.</li>
                <li>Fill in common goals and tasks following the sample rows.</li>
                <li>Use the <strong>Add New Goal</strong> form to enter goals manually, or upload the completed file.</li>
            </ol>
            <div class="mt-2" style="font-size:0.82rem;color:#666;">
                <strong>Template columns:</strong>
                Goal No &bull; Goal Description &bull; Total Score &bull; Year &bull;
                H1 (Y/N) &bull; H2 (Y/N) &bull; Task Description &bull; Task Weightage &bull; Task Target
            </div>
        </div>

        <div class="d-flex gap-2 mb-3">
            <a href="{{ route('commongoal.index', ['year' => $selectedYear]) }}"
               class="btn btn-secondary btn-sm mr-2">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            {{-- Download template (generates a simple CSV in the browser) --}}
            <button type="button" class="btn btn-primary btn-sm" onclick="downloadTemplate()">
                <i class="fa fa-file-excel-o"></i> Download Template (CSV)
            </button>
        </div>

        <hr>
        <p class="text-muted" style="font-size:0.85rem;">
            After filling the template, use the
            <a href="{{ route('commongoal.create', ['year' => $selectedYear]) }}">Add New Goal</a>
            form to enter goals for <strong>{{ $selectedYear }}</strong>.
        </p>
    </div>

</div>
</div>
</div>
@endsection

@section('pagescripts')
<script>
function downloadTemplate() {
    const rows = [
        ['Goal No','Goal Description','Total Score','Year','H1 (Y/N)','H2 (Y/N)','Task Description','Task Weightage','Task Target'],
        ['1','Improve Customer Satisfaction','30','{{ $selectedYear }}','Y','Y','Conduct monthly feedback surveys','10','Q1'],
        ['1','Improve Customer Satisfaction','30','{{ $selectedYear }}','Y','Y','Implement top 3 feedback improvements','10','Q2'],
        ['1','Improve Customer Satisfaction','30','{{ $selectedYear }}','Y','Y','Report satisfaction score','10','Q3'],
        ['2','Operational Efficiency','20','{{ $selectedYear }}','Y','N','Reduce process turnaround time by 15%','10','Q1M1'],
        ['2','Operational Efficiency','20','{{ $selectedYear }}','Y','N','Document all key processes','10','Q2'],
    ];
    const csv = rows.map(r => r.map(c => '"' + String(c).replace(/"/g,'""') + '"').join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url;
    a.download = 'common_goals_template_{{ $selectedYear }}.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>
@endsection
