<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use Illuminate\Http\Response;

final class UnauthorizedException extends BaseException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }
}
