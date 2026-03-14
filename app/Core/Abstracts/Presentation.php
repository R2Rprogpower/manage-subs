<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use App\Core\Interfaces\PresentationInterface;

abstract class Presentation implements PresentationInterface
{
    /**
     * Format data for API response (associative or list).
     *
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array
    {
        if (is_array($data)) {
            return $this->formatArray($data);
        }

        if (is_object($data) && method_exists($data, 'toArray')) {
            return $this->formatArray($data->toArray());
        }

        return ['data' => $data];
    }

    /**
     * @param  array<int|string, mixed>  $array
     * @return array<int|string, mixed>
     */
    protected function formatArray(array $array): array
    {
        $formatted = [];

        foreach ($array as $key => $value) {
            if (is_object($value) && method_exists($value, 'toArray')) {
                $formatted[$key] = $this->formatArray($value->toArray());
            } elseif (is_array($value)) {
                $formatted[$key] = $this->formatArray($value);
            } else {
                $formatted[$key] = $value;
            }
        }

        return $formatted;
    }
}
