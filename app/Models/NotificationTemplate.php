<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'key', 'tool', 'locale', 'type', 'subject', 'body', 'variables', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
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

    public function scopeTool(Builder $query, string $tool): Builder
    {
        return $query->where('tool', $tool);
    }

    public static function findByKey(string $key, string $type = 'email', ?string $locale = null, ?string $tool = null): ?self
    {
        $locale ??= app()->getLocale();

        $query = static::where('key', $key)
            ->where('type', $type)
            ->where('is_active', true);

        if ($tool) {
            $query->where('tool', $tool);
        }

        $localized = (clone $query)->where('locale', $locale)->first();

        if ($localized) {
            return $localized;
        }

        return $query->where('locale', 'en')->first();
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
