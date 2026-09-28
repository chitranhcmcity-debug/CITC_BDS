<?php

/** Bridges PHP warnings, exceptions and fatal errors into structured error_logs. */
class ErrorLogHandler
{
    private static bool $fatalHandled = false;

    public static function register(): void
    {
        set_error_handler(static function (int $severity,string $message,string $file,int $line): bool {
            if (!(error_reporting() & $severity)) return false;
            if (in_array($severity,[E_WARNING,E_USER_WARNING,E_RECOVERABLE_ERROR],true)) {
                SystemLogger::error($message,'php','warning',['file'=>$file,'line'=>$line,'severity'=>$severity]);
            }
            return false;
        });
        register_shutdown_function(static function (): void {
            if (self::$fatalHandled) return;
            $last = error_get_last();
            if ($last && in_array($last['type'],[E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR],true)) {
                self::$fatalHandled = true;
                if (class_exists('SystemLogger')) {
                    SystemLogger::error($last['message'],'php','critical',['file'=>$last['file'],'line'=>$last['line']]);
                    SystemLogger::flush();
                }
            }
        });
    }

    public static function exception(Throwable $exception): void
    {
        if (class_exists('SystemLogger')) {
            SystemLogger::error($exception,self::moduleFromException($exception),'critical',[
                'file'=>$exception->getFile(),'line'=>$exception->getLine()
            ]);
            SystemLogger::flush();
        }
    }

    private static function moduleFromException(Throwable $e): string
    {
        $path = str_replace('\\','/',$e->getFile());
        if (str_contains($path,'/payment/')) return 'payment';
        if (str_contains($path,'/controllers/')) return 'controller';
        if (str_contains($path,'/models/')) return 'database';
        if (str_contains($path,'/services/')) return 'service';
        return 'system';
    }
}
