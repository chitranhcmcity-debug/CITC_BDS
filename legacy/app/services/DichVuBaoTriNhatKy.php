<?php

/** Retention and flat-file rotation, intended for CLI scheduler/cron usage. */
class LogMaintenanceService
{
    public function cleanup(int $months = 12, int $maxFileBytes = 5242880): array
    {
        $months = in_array($months,[6,12,24],true) ? $months : 12;
        $database = (new SystemLogRepository())->purgeBefore(date('Y-m-d H:i:s',strtotime("-{$months} months")));
        return ['database'=>$database,'rotated_files'=>$this->rotateFlatFiles($maxFileBytes)];
    }

    private function rotateFlatFiles(int $maxBytes): array
    {
        $directory = realpath(APP_ROOT.'/logs');
        if (!$directory || !str_starts_with($directory,realpath(APP_ROOT))) return [];
        $rotated=[];
        foreach (['php_error.log','security.log'] as $name) {
            $path=$directory.DIRECTORY_SEPARATOR.$name;
            if (!is_file($path) || filesize($path)<$maxBytes) continue;
            for($i=4;$i>=1;$i--){$from=$path.'.'.$i;$to=$path.'.'.($i+1);if(is_file($from))rename($from,$to);}
            if(rename($path,$path.'.1')){$handle=fopen($path,'wb');if($handle)fclose($handle);$rotated[]=$name;}
        }
        return $rotated;
    }
}
