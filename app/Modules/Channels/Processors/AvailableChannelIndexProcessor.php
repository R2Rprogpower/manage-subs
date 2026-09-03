<?php

declare(strict_types=1);

namespace App\Modules\Channels\Processors;

use App\Core\Abstracts\Processor;
use App\Models\TelegramChannel;
use App\Modules\Channels\Contracts\Services\ChannelServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class AvailableChannelIndexProcessor extends Processor
{
    public function __construct(private readonly ChannelServiceInterface $service) {}

    /** @return Collection<int, TelegramChannel> */
    public function execute(): Collection
    {
        return $this->service->findAvailable();
    }
}
