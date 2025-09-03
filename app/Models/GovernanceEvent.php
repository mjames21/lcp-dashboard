<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GovernanceEvent extends Model
{
    use HasFactory;


    protected $table = 'governance_events';


    protected $fillable = [
        'council_id',
        'title',
        'type',
        'status',
        'occurred_at',
        'location',
        'notes',
    ];


    protected $casts = [
       'occurred_at' => 'date',
    ];


    public function council()
    {
      return $this->belongsTo(LocationCouncils::class, 'council_id');
    }
}