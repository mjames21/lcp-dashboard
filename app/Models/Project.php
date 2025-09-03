<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    /**
     * @var array<int, string>
     */
    public const STATUSES = ['planned','ongoing','completed','stalled'];

    protected $fillable = [
        'council_id',
        'title',
        'status',
        'budget',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
    ];

    public function council()
    {
        return $this->belongsTo(LocationCouncils::class, 'council_id');
    }
}
