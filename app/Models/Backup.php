<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Backup extends Model
{
    protected $fillable = [
        'name',
        'path',
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
        if (Storage::disk('local')->exists($this->path)) {
            Storage::disk('local')->delete($this->path);
        }
    }
}
