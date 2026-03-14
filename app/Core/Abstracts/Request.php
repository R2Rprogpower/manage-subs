<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

use App\Core\Interfaces\RequestInterface;
use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest implements RequestInterface
{
    // Base request class
    // All requests should extend this class
}
