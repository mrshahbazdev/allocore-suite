<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Backup extends Model
{
    protected $fillable = [
        'name',
        'path',
        'disk',
        'type',
        'size',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function deleteFile(): void
    {
        $disk = $this->disk ?? 'local';
        if (Storage::disk($disk)->exists($this->path)) {
            Storage::disk($disk)->delete($this->path);
        }
    }
}
