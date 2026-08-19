<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'required_skills', 'category',
        'location', 'work_type', 'salary', 'application_deadline',
    ];

    protected function casts(): array
    {
        return [
            'salary' => 'decimal:2',
            'application_deadline' => 'date',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function scopeFilter(Builder $query, ?string $search, ?string $category, ?string $workType, ?string $location): Builder
    {
        return $query
            ->when($search, fn (Builder $q) => $q->where('title', 'like', '%'.$search.'%'))
            ->when($category, fn (Builder $q) => $q->where('category', $category))
            ->when($workType, fn (Builder $q) => $q->where('work_type', $workType))
            ->when($location, fn (Builder $q) => $q->where('location', $location));
    }
}
