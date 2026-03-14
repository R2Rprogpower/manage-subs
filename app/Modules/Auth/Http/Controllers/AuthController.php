<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Core\Responses\SuccessResponse;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Http\Requests\MfaSetupRequest;
use App\Modules\Auth\Http\Requests\MfaVerifyRequest;
use App\Modules\Auth\Http\Requests\SignupRequest;
use App\Modules\Auth\Http\Requests\TokenRevokeRequest;
use App\Modules\Auth\Presentations\LoginPresentation;
use App\Modules\Auth\Presentations\LogoutPresentation;
use App\Modules\Auth\Presentations\MfaSetupPresentation;
use App\Modules\Auth\Presentations\MfaVerifyPresentation;
use App\Modules\Auth\Presentations\SignupPresentation;
use App\Modules\Auth\Presentations\TokenRevokePresentation;
use App\Modules\Auth\Processors\LoginProcessor;
use App\Modules\Auth\Processors\LogoutProcessor;
use App\Modules\Auth\Processors\MfaSetupProcessor;
use App\Modules\Auth\Processors\MfaVerifyProcessor;
use App\Modules\Auth\Processors\SignupProcessor;
use App\Modules\Auth\Processors\TokenRevokeProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function signup(
        SignupRequest $request,
        SignupProcessor $processor,
        SignupPresentation $presentation
    ): SuccessResponse {
        $payload = $processor->execute($request);

        return new SuccessResponse(
            $presentation->present($payload),
            ['message' => 'Sign up successful. Complete MFA setup to log in.'],
            Response::HTTP_CREATED
        );
    }

    public function login(
        LoginRequest $request,
        LoginProcessor $processor,
        LoginPresentation $presentation
    ): SuccessResponse {
        $payload = $processor->execute($request);

        return new SuccessResponse(
            $presentation->present($payload),
            ['message' => 'Login successful'],
            Response::HTTP_OK
        );
    }

    public function logout(
        Request $request,
        LogoutProcessor $processor,
        LogoutPresentation $presentation
    ): SuccessResponse {
        $payload = $processor->execute($request);

        return new SuccessResponse(
            $presentation->present($payload),
            ['message' => 'Logout successful'],
            Response::HTTP_OK
        );
    }

    public function mfaSetup(
        MfaSetupRequest $request,
        MfaSetupProcessor $processor,
        MfaSetupPresentation $presentation
    ): SuccessResponse {
        $payload = $processor->execute($request);

        return new SuccessResponse(
            $presentation->present($payload),
            ['message' => 'MFA setup initialized'],
            Response::HTTP_OK
        );
    }

    public function mfaVerify(
        MfaVerifyRequest $request,
        MfaVerifyProcessor $processor,
        MfaVerifyPresentation $presentation
    ): SuccessResponse {
        $payload = $processor->execute($request);

        return new SuccessResponse(
            $presentation->present($payload),
            ['message' => 'MFA verified'],
            Response::HTTP_OK
        );
    }

    public function revokeToken(
        TokenRevokeRequest $request,
        TokenRevokeProcessor $processor,
        TokenRevokePresentation $presentation
    ): SuccessResponse {
        $payload = $processor->execute($request);

        return new SuccessResponse(
            $presentation->present($payload),
            ['message' => 'Token revoked'],
            Response::HTTP_OK
        );
    }
}
