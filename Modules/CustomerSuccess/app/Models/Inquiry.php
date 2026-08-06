<?php

namespace Modules\CustomerSuccess\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\CustomerSuccess\Models\Concerns\BelongsToCurrentTeam;

class Inquiry extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'customersuccess_inquiries';

    protected $fillable = [
        'question',
        'answer',
        'problem',
        'root_cause',
        'consequences',
        'recommended_actions',
        'priority',
        'estimated_cost',
        'expected_benefit',
        'sources',
        'module_key',
    ];

    protected $casts = [
        'sources' => 'array',
    ];
}
