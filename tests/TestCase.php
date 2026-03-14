<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

/**
 * @method TestResponse postJson(string $uri, array $data = [], array $headers = [], int $options = 0)
 * @method TestResponse getJson(string $uri, array $headers = [], int $options = 0)
 * @mixin \Illuminate\Foundation\Testing\Concerns\MakesHttpRequests
 * @mixin \Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase
 */
abstract class TestCase extends BaseTestCase
{
    use MakesHttpRequests;
    use InteractsWithDatabase;
}
