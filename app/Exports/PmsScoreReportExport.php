<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PmsScoreReportExport implements FromView
{
    public function __construct(private array $data) {}

    public function view(): View
    {
        return view('exports.pmsscorereport', $this->data);
    }
}
