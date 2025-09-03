<?php
// app/Models/Indicator.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Indicator extends Model
{
    use HasFactory;

    /**
     * IMPORTANT: Keep column names aligned with your DB.
     * If your table uses different names, adjust $fillable and scopes.
     */
    protected $fillable = [
        'name',        // e.g., "Immunization Coverage"
        'unit',        // e.g., "%"
        'sector_id',   // FK -> sectors.id
        // 'code',      // uncomment if you have a unique code column
        // 'higher_is_better', // uncomment if present (bool)
    ];

    // --- Relationships ---

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(MetricValue::class);
    }

    // --- Scopes ---

    /** Order by display name (fallback to id if name missing in schema). */
    public function scopeOrdered(Builder $query): Builder
    {
        // If your table doesn't have `name`, change to the actual column (e.g., `indicatorname`)
        return $query->when(
            \Schema::hasColumn($this->getTable(), 'name'),
            fn ($q) => $q->orderBy('name'),
            fn ($q) => $q->orderBy('id')
        );
    }

    /** Filter by sector id. */
    public function scopeInSector(Builder $query, int $sectorId): Builder
    {
        return $query->where('sector_id', $sectorId);
    }

    /** Simple name/unit search. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) return $query;
        $like = '%'.trim($term).'%';
        return $query->where(function ($q) use ($like) {
            $q->when(\Schema::hasColumn($this->getTable(), 'name'), fn($qq)=>$qq->where('name', 'like', $like))
              ->when(\Schema::hasColumn($this->getTable(), 'unit'), fn($qq)=>$qq->orWhere('unit', 'like', $like));
        });
    }
}
