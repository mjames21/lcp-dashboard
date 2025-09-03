<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetricValue extends Model
{
    use HasFactory;

    protected $table = 'metric_values';

    protected $fillable = [
        'council_id',
        'indicator_id',
        'period_start',
        'period_end',
        'value',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'value'        => 'decimal:4',
    ];

    /** Keep relation name consistent with your existing LocationCouncils model. */
    public function council()
    {
        return $this->belongsTo(LocationCouncils::class, 'council_id');
    }

    public function indicator()
    {
        return $this->belongsTo(Indicator::class, 'indicator_id');
    }
}
