<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EligibleForLoaExport implements FromView
{
    public function __construct(private array $data) {}

    public function view(): View
    {
        return view('exports.eligibleforloa', $this->data);
    }
}
