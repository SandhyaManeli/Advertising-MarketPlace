<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        // handle 401 errors
        if ($e instanceof HttpException && $e->getStatusCode()==401) {
            return response("Unauthorized", 401);
        }

        // handle 403 errors
        if ($e instanceof HttpException && $e->getStatusCode()==403) {
            return response("Forbidden", 403);
        }
        
        // handle 422 errors
        if ($e instanceof ValidationException) {
            $errors = [];
            $err_data = $e->response->getData();
            foreach($err_data as $k => $v){
                $errors = array_merge($errors, $v);
            }            
            return response()->json(["status" => 0, "message" => $errors]);
        }

        // return response("Internal Server Error", 500);
        return parent::render($request, $e);
    }
}
