<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailSetting extends Model
{
    protected $fillable = [
        'user_id',
        'driver',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'port' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public static function effectiveFor(?User $user): ?self
    {
        if ($user) {
            $setting = static::query()->forUser($user->id)->first();

            if ($setting && $setting->isUsable()) {
                return $setting;
            }
        }

        $global = static::query()->global()->first();

        return $global?->isUsable() ? $global : null;
    }

    public function isUsable(): bool
    {
        return filled($this->host) && filled($this->username);
    }

    public function toMailerConfig(): array
    {
        return [
            'transport' => $this->driver ?? 'smtp',
            'host' => $this->host,
            'port' => $this->port ?? 587,
            'encryption' => $this->encryption,
            'username' => $this->username,
            'password' => $this->password,
            'timeout' => null,
            'local_domain' => parse_url((string) config('app.url', 'http://localhost'), PHP_URL_HOST),
            'from' => [
                'address' => $this->from_address,
                'name' => $this->from_name,
            ],
        ];
    }

    public function displayName(): ?string
    {
        return $this->from_name ?: ($this->user?->name ?: config('app.name'));
    }

    public function displayFrom(): ?string
    {
        return $this->from_address ?: $this->username;
    }
}
