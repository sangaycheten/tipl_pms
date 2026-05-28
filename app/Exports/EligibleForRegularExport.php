<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EligibleForRegularExport implements FromView
{
    public function __construct(private array $data) {}

    public function view(): View
    {
        return view('exports.eligibleforregular', $this->data);
    }
}
