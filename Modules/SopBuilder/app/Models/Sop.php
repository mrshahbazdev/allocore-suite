<?php

namespace Modules\SopBuilder\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\SopBuilder\Models\Concerns\BelongsToCurrentTeam;

class Sop extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'sops';

    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
        'version' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(Step::class)->orderBy('sort_order');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('sort_order');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class)->orderBy('sort_order');
    }

    public function completions(): HasMany
    {
        return $this->hasMany(Completion::class);
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
