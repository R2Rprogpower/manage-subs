<?php

declare(strict_types=1);

namespace App\Modules\Channels\Http\Requests;

use App\Core\Abstracts\Request;
use App\Models\TelegramChannel;

class DeleteChannelRequest extends Request
{
    public function authorize(): bool
    {
        $channel = TelegramChannel::query()->find((int) $this->route('id'));

        return $channel !== null && ($this->user()?->can('delete', $channel) ?? false);
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [];
    }
}
