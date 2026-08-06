<?php

namespace Modules\PlanHive\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Modules\PlanHive\Models\Concerns\BelongsToCurrentTeam;

class Document extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'planhive_documents';

    protected $fillable = [
        'team_id',
        'project_id',
        'user_id',
        'title',
        'path',
        'mime_type',
        'size',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'position' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getReadableSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $i = 0;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2).' '.$units[$i];
    }

    public function isImage(): bool
    {
        return $this->mime_type && str_starts_with($this->mime_type, 'image/');
    }

    public function fileUrl(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
