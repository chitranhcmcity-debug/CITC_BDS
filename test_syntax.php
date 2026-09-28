<?php

declare(strict_types=1);

$root = __DIR__;
$excludedDirectories = [
    '.codegraph',
    'node_modules',
    'storage',
    'vendor',
];
$failures = [];
$checked = 0;

$directory = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
$filter = new RecursiveCallbackFilterIterator(
    $directory,
    static function (SplFileInfo $item) use ($excludedDirectories): bool {
        return ! $item->isDir() || ! in_array($item->getFilename(), $excludedDirectories, true);
    }
);
$files = new RecursiveIteratorIterator($filter);

foreach ($files as $file) {
    if (! $file instanceof SplFileInfo
        || ! $file->isFile()
        || strtolower($file->getExtension()) !== 'php'
        || str_ends_with(strtolower($file->getFilename()), '.blade.php')) {
        continue;
    }

    $checked++;
    $pipes = [];
    $process = proc_open(
        [PHP_BINARY, '-l', $file->getPathname()],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes
    );
    if (! is_resource($process)) {
        $failures[] = $file->getPathname().': không thể khởi chạy PHP lint.';

        continue;
    }

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    if ($exitCode !== 0) {
        $failures[] = trim($stdout.PHP_EOL.$stderr);
    }
}

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL.PHP_EOL, $failures).PHP_EOL);
    fwrite(STDERR, 'PHP lint thất bại: '.count($failures)."/{$checked} tệp lỗi.".PHP_EOL);
    exit(1);
}

echo "PHP lint thành công: {$checked} tệp.".PHP_EOL;
