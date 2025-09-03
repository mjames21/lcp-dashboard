?>

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectUpdate extends Model
{
    use HasFactory;

    protected $table = 'project_updates';

    protected $fillable = [
        'project_id',
        'status',
        'progress_percent',
        'amount_spent',
        'reported_at',
        'notes',
    ];

    protected $casts = [
        'progress_percent' => 'integer',
        'amount_spent'     => 'decimal:2',
        'reported_at'      => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
