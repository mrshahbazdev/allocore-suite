<?php

namespace Modules\FocusMatrix\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class AiSetting extends Model
{
    use HasFactory;

    public const PROVIDERS = [
        'gemini' => 'Google Gemini',
        'openai' => 'OpenAI (GPT)',
        'anthropic' => 'Anthropic (Claude)',
    ];

    public const DEFAULT_MODELS = [
        'gemini' => 'gemini-2.0-flash',
        'openai' => 'gpt-4o-mini',
        'anthropic' => 'claude-3-5-sonnet-latest',
    ];

    public const MODEL_OPTIONS = [
        'gemini' => ['gemini-2.0-flash', 'gemini-1.5-pro', 'gemini-1.5-flash'],
        'openai' => ['gpt-4o-mini', 'gpt-4o', 'gpt-4-turbo', 'gpt-3.5-turbo'],
        'anthropic' => ['claude-3-5-sonnet-latest', 'claude-3-5-haiku-latest', 'claude-3-opus-latest'],
    ];

    protected $table = 'focusmatrix_ai_settings';

    protected $fillable = [
        'user_id', 'provider', 'api_key_encrypted', 'model', 'enabled',
        'calls_this_month', 'monthly_limit', 'last_called_at', 'quota_reset_at', 'meta',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'meta' => 'array',
        'last_called_at' => 'datetime',
        'quota_reset_at' => 'datetime',
    ];

    protected $hidden = ['api_key_encrypted'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getApiKey(): ?string
    {
        if (! $this->api_key_encrypted) {
            return null;
        }
        try {
            return Crypt::decryptString($this->api_key_encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setApiKeyAttribute(?string $value): void
    {
        $this->attributes['api_key_encrypted'] = $value ? Crypt::encryptString($value) : null;
    }

    public function hasKey(): bool
    {
        return ! empty($this->api_key_encrypted);
    }

    public function maskedKey(): ?string
    {
        $key = $this->getApiKey();
        if (! $key) {
            return null;
        }
        $len = strlen($key);
        if ($len <= 8) {
            return str_repeat('•', $len);
        }

        return substr($key, 0, 4).str_repeat('•', max(4, $len - 8)).substr($key, -4);
    }

    public function remainingQuota(): int
    {
        return max(0, $this->monthly_limit - $this->calls_this_month);
    }

    public function registerCall(): void
    {
        $now = now();
        if ($this->quota_reset_at && $now->greaterThan($this->quota_reset_at)) {
            $this->calls_this_month = 0;
            $this->quota_reset_at = $now->copy()->addMonth()->startOfMonth();
        }
        if (! $this->quota_reset_at) {
            $this->quota_reset_at = $now->copy()->addMonth()->startOfMonth();
        }
        $this->calls_this_month++;
        $this->last_called_at = $now;
        $this->save();
    }
}
