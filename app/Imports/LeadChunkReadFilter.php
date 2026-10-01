<?php

namespace App\Imports;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class LeadChunkReadFilter implements IReadFilter
{
    private int $startRow = 1;
    private int $endRow = 1;

    public function setRows(int $startRow, int $rowCount): void
    {
        $this->startRow = $startRow;
        $this->endRow = $startRow + $rowCount - 1;
    }

    public function readCell($columnAddress, $row, $worksheetName = '')
    {
        return $row === 1 || ($row >= $this->startRow && $row <= $this->endRow);
    }
}
