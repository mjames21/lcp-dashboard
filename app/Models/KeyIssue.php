<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeyIssue extends Model
{
    protected $table = 'key_issues';

    protected $fillable = [
        'council_id',
        'title',
        'description',
        'owner',
        'priority',     // low|medium|high
        'status',       // open|in_progress|blocked|resolved|closed
        'opened_at',
        'due_at',
        'closed_at',
        'resolved_at',
        'source',
        'tags',
    ];

    protected $casts = [
        'opened_at'  => 'datetime',
        'due_at'     => 'datetime',
        'closed_at'  => 'datetime',
        'resolved_at'=> 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function council()
    {
        return $this->belongsTo(\App\Models\LocationCouncils::class, 'council_id');
    }
}
