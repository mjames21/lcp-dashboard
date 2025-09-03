<?php

// app/Models/FinanceEntry.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceEntry extends Model
{
    protected $fillable = [
        'council_id','category','sub_category','period_start','period_end','amount',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'amount'       => 'decimal:2',
    ];

    public function council()
    {
        return $this->belongsTo(LocationCouncils::class, 'council_id');
    }
}
