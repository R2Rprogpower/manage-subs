<?php

declare(strict_types=1);

namespace App\Modules\Channels\Http\Requests;

use App\Core\Abstracts\Request;
use App\Models\TelegramChannel;
use App\Modules\Channels\Enums\ChannelStatus;
use Illuminate\Validation\Rule;

class UpdateChannelRequest extends Request
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('username'))) {
            $this->merge(['username' => ltrim($this->input('username'), '@')]);
        }
    }

    public function authorize(): bool
    {
        $channel = TelegramChannel::query()->find((int) $this->route('id'));

        return $channel !== null && ($this->user()?->can('update', $channel) ?? false);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $channelId = (int) $this->route('id');

        return [
            'telegram_chat_id' => ['sometimes', 'string', 'max:100', Rule::unique('telegram_channels')->ignore($channelId)],
            'username' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9_]{5,}$/', Rule::unique('telegram_channels')->ignore($channelId)],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'string', Rule::in(ChannelStatus::values())],
        ];
    }
}
