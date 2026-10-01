<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadImport extends Model
{
    protected $fillable = [
        'id', 'file_path', 'status', 'total_rows', 'processed_rows',
        'inserted_rows', 'next_row', 'error',
    ];

    public $incrementing = false;

    protected $keyType = 'string';
}
