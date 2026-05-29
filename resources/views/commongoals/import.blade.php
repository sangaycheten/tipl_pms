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
                <li>Download the template CSV below.</li>
                <li>Fill in common goals and tasks following the sample rows.</li>
                <li>Upload the completed file using the form below.</li>
            </ol>
            <div class="mt-2" style="font-size:0.82rem;color:#666;">
                <strong>Template columns:</strong>
                Goal Description &bull; Total Score &bull; Year &bull;
                H1 (Y/N) &bull; H2 (Y/N) &bull; Task Description &bull; Task Weightage &bull; Task Target
            </div>
            <div class="mt-1" style="font-size:0.82rem;color:#888;">
                <i class="fa fa-info-circle"></i>
                For goals with multiple tasks, fill Goal Description / Total Score / Year / H1 / H2 only on the <strong>first row</strong> — leave them blank for subsequent task rows of the same goal.
            </div>
        </div>

        <div class="d-flex align-items-center mb-3" style="gap:8px;">
            <a href="{{ route('commongoal.index', ['year' => $selectedYear]) }}"
               class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            <button type="button" class="btn btn-default btn-sm" onclick="downloadTemplate()"
                    style="border:1px solid #ccc;">
                <i class="fa fa-file-excel-o"></i> Download Template (CSV)
            </button>
        </div>

        <hr>

        {{-- Error messages --}}
        @if($errors->any())
        <div class="alert alert-danger" style="font-size:0.88rem;">
            <ul class="mb-0" style="padding-left:16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Upload form --}}
        <form method="POST" action="{{ route('commongoal.import.post') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="year" value="{{ $selectedYear }}">

            <div class="form-group">
                <label style="font-weight:600; color:#226b86;">
                    <i class="fa fa-upload mr-1"></i> Upload Filled CSV File
                </label>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="importFile" name="file"
                               accept=".csv,.txt" required>
                        <label class="custom-file-label" for="importFile">Choose CSV file…</label>
                    </div>
                </div>
                <small class="text-muted">Accepted format: .csv</small>
            </div>

            <button type="submit" class="btn btn-success btn-sm">
                <i class="fa fa-upload"></i> Import Goals
            </button>
        </form>
    </div>

</div>
</div>
</div>
@endsection

@section('pagescripts')
<script>
document.getElementById('importFile').addEventListener('change', function () {
    var label = this.nextElementSibling;
    label.textContent = this.files.length ? this.files[0].name : 'Choose CSV file…';
});

function downloadTemplate() {
    const rows = [
        ['Goal Description','Total Score','Year','H1 (Y/N)','H2 (Y/N)','Task Description','Task Weightage','Task Target'],
        ['Improve Customer Satisfaction','30','{{ $selectedYear }}','Y','Y','Conduct monthly feedback surveys','10','Q1M1'],
        ['','','','','','Implement top 3 feedback improvements','10','Q1M2'],
        ['','','','','','Report satisfaction score','10','Q2M1'],
        ['Operational Efficiency','20','{{ $selectedYear }}','Y','N','Reduce process turnaround time by 15%','10','Q1M1'],
        ['','','','','','Document all key processes','10','Q2M1'],
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
