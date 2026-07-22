<?php

namespace Modules\BunnyBand\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\BunnyBand\Models\Concerns\BelongsToCurrentTeam;

class BunnyBandProfile extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bunnyband_profiles';

    protected $fillable = [
        'team_id', 'user_id', 'level_id', 'referral_code', 'referred_by',
        'balance', 'task_earnings', 'referral_earnings', 'total_referrals',
        'is_blocked', 'level_upgraded_at', 'ip_address', 'last_login_at',
    ];

    protected $casts = [
        'balance' => 'float',
        'task_earnings' => 'float',
        'referral_earnings' => 'float',
        'is_blocked' => 'boolean',
        'level_upgraded_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $profile): void {
            if (! $profile->referral_code) {
                $profile->referral_code = self::generateReferralCode();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referred_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(UserTask::class, 'bunnyband_profile_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'bunnyband_profile_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'bunnyband_profile_id');
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'bunnyband_profile_id');
    }

    public static function forCurrentUser(): ?self
    {
        $user = auth()->user();
        if (! $user?->current_team_id) {
            return null;
        }

        return self::firstOrCreate(
            ['team_id' => $user->current_team_id, 'user_id' => $user->id],
            ['level_id' => Level::where('team_id', $user->current_team_id)->where('type', 'free')->orderBy('sort_order')->value('id')]
        );
    }

    public static function generateReferralCode(): string
    {
        do {
            $code = 'BNY-'.strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }
}
