<?php

declare(strict_types=1);

namespace App\Modules\Channels\Http\Requests;

use App\Core\Abstracts\Request;
use App\Modules\Channels\Enums\ChannelStatus;
use Illuminate\Validation\Rule;

class StoreChannelRequest extends Request
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('username'))) {
            $this->merge(['username' => ltrim($this->input('username'), '@')]);
        }
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'telegram_chat_id' => ['required', 'string', 'max:100', 'unique:telegram_channels,telegram_chat_id'],
            'username' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9_]{5,}$/', 'unique:telegram_channels,username'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'string', Rule::in(ChannelStatus::values())],
        ];
    }
}
