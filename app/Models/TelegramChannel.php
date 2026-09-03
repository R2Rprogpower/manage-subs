<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TelegramChannelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $telegram_chat_id
 * @property string|null $username
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $owner
 * @property-read Collection<int, Plan> $plans
 */
class TelegramChannel extends Model
{
    /** @use HasFactory<TelegramChannelFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'owner_id',
        'telegram_chat_id',
        'username',
        'title',
        'description',
        'status',
    ];

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return HasMany<Plan, $this> */
    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }
}
