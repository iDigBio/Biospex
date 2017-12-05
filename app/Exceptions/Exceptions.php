<?php namespace App\Exceptions;

use App\Mail\BiospexException as BiospexExceptionMail;
use Exception;
use Mail;

class BiospexException extends Exception
{
    public function report()
    {
        $exception = [
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'message' => $this->getMessage(),
            'trace' => $this->getTraceAsString()
        ];

        Mail::to(config('mail.from.address'))->send(new BiospexExceptionMail($exception));
    }
}

class RequestException extends BiospexException
{

}

class ThumbnailFromUrlException extends BiospexException
{

}

class FileDoesNotExist extends BiospexException
{

}

class MetaFileException extends BiospexException
{

}

class DownloadFileException extends BiospexException
{

}

class FileTypeException extends BiospexException
{

}

class FileSaveException extends BiospexException
{

}

class CreateDirectoryException extends BiospexException
{

}

class CsvHeaderCountException extends BiospexException
{

}

class CsvHeaderNameException extends BiospexException
{

}

class FileUnzipException extends BiospexException
{

}

class XmlLoadException extends BiospexException
{

}

class RowTypeMismatchException extends BiospexException
{

}

class MissingNodeException extends BiospexException
{

}

class MissingCsvDelimiter extends BiospexException
{

}

class MissingMetaIdentifier extends BiospexException
{

}

class ExtensionMissingException extends BiospexException
{

}

class OcrBatchProcessException extends BiospexException
{

}

class MongoDbException extends BiospexException
{

}

class NfnApiException extends BiospexException
{

}

class HttpRequestException extends BiospexException
{

}

class GoogleFusionTableException extends BiospexException
{

}

class ActorException extends BiospexException
{

}


