<?php

namespace App\Jobs;

use App\Imports\LeadsImport as LeadsFileImporter;
use App\Models\LeadImport as LeadImportRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessLeadImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 0;
    public int $tries = 1;

    public function __construct(public string $importId)
    {
    }

    public function handle(LeadsFileImporter $importer): void
    {
        $record = LeadImportRecord::findOrFail($this->importId);
        $record->update(['status' => 'processing', 'error' => null]);
        $absolutePath = Storage::disk('local')->path($record->file_path);

        try {
            while ($record->next_row <= $record->total_rows + 1) {
                $startRow = $record->next_row;
                $rowCount = min(LeadsFileImporter::CHUNK_SIZE, $record->total_rows - $startRow + 2);

                DB::transaction(function () use ($record, $importer, $absolutePath, $startRow, $rowCount) {
                    $inserted = $importer->importChunk($absolutePath, $startRow, $rowCount);
                    $record->processed_rows = min($record->total_rows, $startRow + $rowCount - 2);
                    $record->inserted_rows += $inserted;
                    $record->next_row = $startRow + $rowCount;
                    $record->save();
                });

                $record->refresh();
            }

            $record->update(['status' => 'completed']);
            Storage::disk('local')->delete($record->file_path);
        } catch (Throwable $exception) {
            $record->update(['status' => 'failed', 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }
}
