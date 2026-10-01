<?php

namespace App\Http\Controllers;

use App\Imports\LeadsImport;
use App\Jobs\ProcessLeadImport;
use App\Models\LeadImport as LeadImportRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LeadImportController extends Controller
{
    public function index()
    {
        return view('leads.import');
    }

    public function import(Request $request, LeadsImport $import)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx'],
        ]);

        $importId = (string) Str::uuid();
        $path = $request->file('file')->storeAs('imports', $importId.'.xlsx', 'local');
        $totalRows = max(0, $import->rowCount(Storage::disk('local')->path($path)) - 1);

        LeadImportRecord::create([
            'id' => $importId,
            'file_path' => $path,
            'total_rows' => $totalRows,
        ]);

        ProcessLeadImport::dispatch($importId);

        return redirect()
            ->route('leads.import')
            ->with('success', "Файл на {$totalRows} рядків завантажено. Імпорт виконується у фоновому режимі.");
    }
}
