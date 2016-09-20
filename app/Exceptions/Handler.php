<?php namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Config;
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
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param Exception $e
     */
    public function report(Exception $e)
    {
        switch ($e)
        {
            case $e instanceof DownloadCleanCommandException:
            case $e instanceof OcrProcessCommandException:
            case $e instanceof OcrRequestOcrFileException:
            case $e instanceof OcrRequestException:
            case $e instanceof ExpeditionProcessException:
            case $e instanceof ExpeditionDeleteException:
            case $e instanceof RegisterUserException:
            case $e instanceof ActorImageServiceSaveImageException:
            case $e instanceof NfnLegacyExportException:
            case $e instanceof NfnPanoptesClassificationsException:
            case $e instanceof NfnPanoptesExportException:
            case $e instanceof ThumbnailCreateException:
            case $e instanceof ActorFactoryCreateException:
            case $e instanceof DarwinCoreFileImportQueueException:
            case $e instanceof DarwinCoreUrlImportQueueException:
            case $e instanceof CreateDirectoryException:
            case $e instanceof DownloadFileException:
            case $e instanceof FileTypeException:
            case $e instanceof FileSaveException:
            case $e instanceof NfnTranscriptionQueueException:
            case $e instanceof RecordSetQueueException:

                $tube = Config::get('config.beanstalkd.default');
                $view = 'frontend.emails.exception';
                $data = ['error' => jTraceEx($e)];
                $email = Config::get('mail.from.address');
                $name = Config::get('mail.from.name');

                Mail::queueOn($tube, $view, $data, function ($message) use ($email, $name) {
                    $message->from($email, $name)->subject('Thrown Exception')->to($email);
                });

                break;
        }

        return parent::report($e);
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
