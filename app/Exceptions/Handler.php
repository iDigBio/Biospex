<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Mail;

class Handler extends ExceptionHandler
{

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        'Symfony\Component\HttpKernel\Exception\HttpException'
    ];

    /**
     * Report or log an exception.
     *
     * @param Exception $e
     */
    public function report(Exception $e)
    {
        if ($e instanceof BiospexException)
        {
            $tube = config('config.beanstalkd.default');
            $view = 'frontend.emails.exception';
            $email = config('mail.from');
            $data = ['error' => jTraceEx($e)];

            Mail::queueOn($tube, $view, $data, function ($message) use ($email)
            {
                $message->from($email['address'], $email['name'])->subject('Thrown Exception')->to($email['address']);
            });

        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        return parent::render($request, $e);
    }
}
