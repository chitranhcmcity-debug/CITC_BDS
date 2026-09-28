<?php
class SystemCacheService
{
 public function clear(string $type):int{$dirs=match($type){'view'=>[APP_ROOT.'/storage/cache/views'],'route'=>[APP_ROOT.'/storage/cache/routes'],'config'=>[APP_ROOT.'/storage/cache/config'],default=>[APP_ROOT.'/storage/cache']};$n=0;foreach($dirs as$d)if(is_dir($d))foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($d,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST)as$f)if($f->isFile()&&@unlink($f->getPathname()))$n++;if(function_exists('opcache_reset'))@opcache_reset();return$n;}
}
