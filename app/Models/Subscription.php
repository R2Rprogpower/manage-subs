<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $plan_id
 * @property string $status
 * @property Carbon $started_at
 * @property Carbon|null $activated_at
 * @property Carbon|null $suspended_at
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $ends_at
 * @property bool $auto_renew
 * @property bool $trial_used
 * @property string $source
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Plan|null $plan
 * @property-read Collection<int, Payment> $payments
 */
class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'started_at',
        'activated_at',
        'suspended_at',
        'cancelled_at',
        'ends_at',
        'auto_renew',
        'trial_used',
        'source',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'ends_at' => 'datetime',
            'auto_renew' => 'boolean',
            'trial_used' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function grantsAccess(?Carbon $at = null): bool
    {
        $moment = $at ?? now();

        return $this->status === 'active' && ($this->ends_at === null || $this->ends_at->isAfter($moment));
    }
}
