<?php

namespace App\Exceptions;

use App\Models\SystemLog;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDOException;
use Symfony\Component\HttpFoundation\Response;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        TransferException::class,
        LockException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Get the default context variables for logging.
     *
     * @return array
     */
    protected function context(): array
    {
        return rescue(fn () => array_filter([
            'user' => Auth::user()?->only('id', 'name'),
        ]), [], false);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  Request  $request
     * @param  AuthenticationException  $exception
     * @return Response
     */
    protected function unauthenticated($request, AuthenticationException $exception): Response
    {
        return !$request->expectsJson()
            ? redirect()->guest($exception->redirectTo() ?? route('main'))
                ->notify(trans('auth.unauthenticated'), 'error')
            : response()->json(['message' => $exception->getMessage()], 401);
    }

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function (Exception $exception) {
            if (!$exception instanceof PDOException) {
                SystemLog::error($exception->getMessage());
            }
        });
    }
}
