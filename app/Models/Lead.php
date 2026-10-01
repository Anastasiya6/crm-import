<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'external_id',
        'lead_created_at',
        'first_name',
        'last_name',
        'phone',
        'email',
        'city',
        'source',
        'utm_campaign',
        'product',
        'budget_uah',
        'status',
        'manager',
        'comment',
        'next_contact_at',
    ];
}
