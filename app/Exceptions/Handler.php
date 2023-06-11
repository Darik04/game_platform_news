<?php

namespace App\Exceptions;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }





    public function render($request, Throwable $exception) {
        if ($exception instanceof NotFoundHttpException || $exception instanceof ModelNotFoundException) {
            return response()->json([
                'status' => 'not-found',
                'message' => 'Not found',
            ], 404);
        } else if ($exception instanceof ValidationException) {
            $violations = collect($exception->validator->errors())->map(function ($e) {
                return ['message' => $e[0]];
            });
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request body is not valid.',
                'violations' => $violations,
            ], 400);
        } else if ($exception instanceof AuthenticationException) {
            // no header specified
            if (!$request->headers->get('Authorization')) {
                return response()->json([
                    'status' => 'unauthenticated',
                    'message' => 'Missing token',
                ], 401);
            } else {
                $token = PersonalAccessToken::findToken(explode(' ', $request->headers->get('Authorization'))[1]);

                if ($token) {
                    $user = $token->tokenable()->withTrashed()->first();

                    // user got blocked
                    if ($user && $user->deleted_at !== null) {
                        return response()->json([
                            'status' => 'blocked',
                            'message' => 'User blocked',
                            'reason' => User::$DELETE_REASONS[$user->delete_reason] ?? null,
                        ]);
                    }
                }

                // invalid token
                return response()->json([
                    'status' => 'unauthenticated',
                    'message' => 'Invalid token',
                ], 401);
            }
        }

        return parent::render($request, $exception);
    }
}
