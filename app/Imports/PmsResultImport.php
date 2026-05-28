<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class PmsResultImport implements ToArray
{
    public array $sheets = [];

    public function array(array $array): void
    {
        $this->sheets[] = $array;
    }
}
