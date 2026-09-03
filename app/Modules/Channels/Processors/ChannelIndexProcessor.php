<?php

declare(strict_types=1);

namespace App\Modules\Channels\Processors;

use App\Core\Abstracts\Processor;
use App\Models\TelegramChannel;
use App\Models\User;
use App\Modules\Channels\Contracts\Services\ChannelServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class ChannelIndexProcessor extends Processor
{
    public function __construct(private readonly ChannelServiceInterface $service) {}

    /** @return Collection<int, TelegramChannel> */
    public function execute(User $user): Collection
    {
        return $this->service->findVisibleTo($user);
    }
}
