<?php

declare(strict_types=1);

namespace App\Modules\Channels\Processors;

use App\Core\Abstracts\Processor;
use App\Modules\Channels\Contracts\Services\ChannelServiceInterface;

class ChannelDestroyProcessor extends Processor
{
    public function __construct(private readonly ChannelServiceInterface $service) {}

    public function execute(int $id): bool
    {
        $this->service->delete($id);

        return true;
    }
}
