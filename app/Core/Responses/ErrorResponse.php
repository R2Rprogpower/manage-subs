<?php

declare(strict_types=1);

namespace App\Core\Responses;

use App\Core\Exceptions\BaseException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Throwable;

class ErrorResponse implements Responsable
{
    /**
     * @param  array<string, string>  $headers
     */
    public function __construct(
        private readonly string $message,
        private readonly ?Throwable $exception = null,
        private readonly int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        private readonly array $headers = []
    ) {}

    public function toResponse($request): JsonResponse
    {
        $code = $this->code;
        $response = ['message' => $this->message];

        $errorLevel = 'error';
        $withTrace = true;

        if ($this->exception instanceof ValidationException) {
            $response['errors'] = $this->exception->errors();
            $response['message'] = $this->exception->getMessage();
            $code = Response::HTTP_UNPROCESSABLE_ENTITY;
            $errorLevel = 'warning';
            $withTrace = false;
        } elseif ($this->exception instanceof BaseException) {
            $response['message'] = $this->exception->getMessage();
            $code = $this->exception->getStatusCode();
            $errorLevel = 'warning';
            $withTrace = false;
        } elseif ($this->exception instanceof ModelNotFoundException) {
            $response['message'] = 'Resource not found';
            $code = Response::HTTP_NOT_FOUND;
            $errorLevel = 'warning';
            $withTrace = false;
        } elseif ($this->exception instanceof \InvalidArgumentException) {
            $response['message'] = $this->exception->getMessage();
            $code = Response::HTTP_BAD_REQUEST;
            $errorLevel = 'warning';
            $withTrace = false;
        }

        if ($this->exception !== null && config('app.debug') && $withTrace) {
            $response['debug'] = [
                'message' => $this->exception->getMessage(),
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
                'trace' => $this->getTrace($this->exception),
            ];
        }

        return response()->json($response, $code, $this->headers);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getTrace(Throwable $exception): array
    {
        $trace = [];
        foreach ($exception->getTrace() as $item) {
            if (isset($item['class']) && str_contains($item['class'], 'Illuminate\\Routing\\Controller')) {
                break;
            }

            $traceItem = [
                'file' => $item['file'] ?? null,
                'line' => $item['line'] ?? null,
                'function' => $item['function'],
            ];

            if (isset($item['class'])) {
                $traceItem['class'] = $item['class'];
            }

            $trace[] = $traceItem;
        }

        return $trace;
    }
}
