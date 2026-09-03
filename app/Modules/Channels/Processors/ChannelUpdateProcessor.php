<?php

declare(strict_types=1);

namespace App\Modules\Channels\Processors;

use App\Core\Abstracts\Processor;
use App\Models\TelegramChannel;
use App\Modules\Channels\Contracts\Services\ChannelServiceInterface;
use App\Modules\Channels\DTO\UpdateChannelDTO;
use App\Modules\Channels\Http\Requests\UpdateChannelRequest;

class ChannelUpdateProcessor extends Processor
{
    public function __construct(private readonly ChannelServiceInterface $service) {}

    public function execute(UpdateChannelRequest $request, int $id): TelegramChannel
    {
        $data = $request->validated();
        if (isset($data['username'])) {
            $data['username'] = ltrim($data['username'], '@');
        }

        return $this->service->update($id, new UpdateChannelDTO($data));
    }
}
