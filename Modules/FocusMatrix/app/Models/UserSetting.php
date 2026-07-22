<?php

namespace Modules\FocusMatrix\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserSetting extends Model
{
    use HasFactory;

    protected $table = 'focusmatrix_user_settings';

    protected $fillable = ['user_id', 'ics_token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function forUser(User $user): self
    {
        return static::firstOrCreate(
            ['user_id' => $user->id],
            ['ics_token' => Str::random(48)]
        );
    }
}
