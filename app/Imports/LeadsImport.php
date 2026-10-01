<?php

namespace App\Imports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LeadsImport
{
    public const CHUNK_SIZE = 5000;
    private const INSERT_SIZE = 500;

    public function rowCount(string $filePath): int
    {
        $reader = new Xlsx();
        $sheets = $reader->listWorksheetInfo($filePath);

        return (int) ($sheets[0]['totalRows'] ?? 0);
    }

    public function importChunk(string $filePath, int $startRow, int $rowCount): int
    {
        $reader = new Xlsx();
        $reader->setReadDataOnly(true);

        $filter = new LeadChunkReadFilter();
        $filter->setRows($startRow, $rowCount);
        $reader->setReadFilter($filter);

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $headers = [];
        $header = $sheet->getRowIterator(1, 1)->current();
        $headerCells = $header->getCellIterator();
        $headerCells->setIterateOnlyExistingCells(false);

        foreach ($headerCells as $cell) {
            $headers[] = $cell->getValue();
        }

        $inserted = 0;
        $batch = [];

        foreach ($sheet->getRowIterator($startRow, $startRow + $rowCount - 1) as $row) {
            $rowData = [];
            $cells = $row->getCellIterator();
            $cells->setIterateOnlyExistingCells(false);

            foreach ($cells as $cell) {
                $rowData[] = $cell->getValue();
            }

            if ($this->isEmptyRow($rowData)) {
                continue;
            }

            $batch[] = $this->mapRow($headers, $rowData);

            if (count($batch) >= self::INSERT_SIZE) {
                DB::table('leads')->insert($batch);
                $inserted += count($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            DB::table('leads')->insert($batch);
            $inserted += count($batch);
        }

        $spreadsheet->disconnectWorksheets();

        return $inserted;
    }

    private function mapRow(array $headers, array $row): array
    {
        $row = array_pad($row, count($headers), null);
        $data = array_combine($headers, array_slice($row, 0, count($headers)));

        return [
            'external_id' => $data['external_id'] ?? null,
            'lead_created_at' => $this->parseDate($data['created_at'] ?? null),
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'city' => $data['city'] ?? null,
            'source' => $data['source'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'product' => $data['product'] ?? null,
            'budget_uah' => $data['budget_uah'] ?? null,
            'status' => $data['status'] ?? null,
            'manager' => $data['manager'] ?? null,
            'comment' => $data['comment'] ?? null,
            'next_contact_at' => $this->parseDate($data['next_contact_at'] ?? null),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function parseDate($value): ?\DateTimeInterface
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value)
            ? Date::excelToDateTimeObject($value)
            : new \DateTime((string) $value);
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }
}
