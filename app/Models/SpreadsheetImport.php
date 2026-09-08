<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpreadsheetImport extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array',
        'options' => 'array',
        'results' => 'array',
        'completed_at' => 'datetime',
    ];
}
