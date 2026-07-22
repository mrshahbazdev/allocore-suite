<?php

namespace Modules\LoopEngine\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\LoopEngine\Models\Concerns\BelongsToCurrentTeam;

class ProcessTemplate extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'loopengine_process_templates';

    protected $fillable = [
        'team_id',
        'process_id',
        'shared_by',
        'name_en',
        'name_de',
        'description_en',
        'description_de',
        'category',
        'tags',
        'is_public',
        'install_count',
        'rating',
        'rating_count',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_public' => 'boolean',
            'install_count' => 'integer',
            'rating_count' => 'integer',
            'rating' => 'decimal:2',
        ];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class, 'process_id');
    }

    public function sharedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(TemplateRating::class, 'template_id');
    }

    public function localizedName(): string
    {
        return app()->getLocale() === 'de' && $this->name_de ? $this->name_de : $this->name_en;
    }

    public function localizedDescription(): ?string
    {
        return app()->getLocale() === 'de' && $this->description_de ? $this->description_de : $this->description_en;
    }

    public function recalculateRating(): void
    {
        $this->rating = $this->ratings()->avg('rating') ?: 0;
        $this->rating_count = $this->ratings()->count();
        $this->save();
    }
}
