<?php

namespace App\Services;

use App\Repositories\BackupRepository;

class SystemBackupService
{
    private BackupRepository $repo;

    private string $dir;

    public function __construct()
    {
        $this->repo = new BackupRepository;
        $this->dir = APP_ROOT.'/storage/backups';
        if (! is_dir($this->dir)) {
            mkdir($this->dir, 0750, true);
        }
    }

    public function database(int $user): bool
    {
        $name = 'database_'.date('Ymd_His').'.sql';
        $file = $this->dir.'/'.$name;
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $out = "SET FOREIGN_KEY_CHECKS=0;\n";
        foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $t) {
            $create = $pdo->query('SHOW CREATE TABLE `'.$t.'`')->fetch(PDO::FETCH_NUM)[1];
            $out .= "DROP TABLE IF EXISTS `{$t}`;\n{$create};\n";
            foreach ($pdo->query('SELECT * FROM `'.$t.'`', PDO::FETCH_ASSOC) as $row) {
                $vals = array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), array_values($row));
                $out .= 'INSERT INTO `'.$t.'` (`'.implode('`,`', array_keys($row)).'`) VALUES('.implode(',', $vals).");\n";
            }
        }$out .= "SET FOREIGN_KEY_CHECKS=1;\n";
        if (file_put_contents($file, $out) === false) {
            return false;
        }

        return $this->repo->add('database', $name, filesize($file), $user);
    }

    public function create(string $type, int $user): bool
    {
        if ($type === 'database') {
            return $this->database($user);
        }if (! class_exists('ZipArchive')) {
            return false;
        }
        $type = in_array($type, ['uploads', 'source', 'full'], true) ? $type : 'database';
        $name = $type.'_'.date('Ymd_His').'.zip';
        $path = $this->dir.'/'.$name;
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        $roots = match ($type) {
            'uploads' => [APP_ROOT.'/public/uploads' => 'uploads'],'source' => [APP_ROOT.'/app' => 'app', APP_ROOT.'/core' => 'core', APP_ROOT.'/config' => 'config'],default => [APP_ROOT.'/app' => 'app', APP_ROOT.'/core' => 'core', APP_ROOT.'/config' => 'config', APP_ROOT.'/public/uploads' => 'uploads']
        };
        foreach ($roots as $root => $prefix) {
            $this->addDirectory($zip, $root, $prefix);
        }$zip->close();

        return is_file($path) && $this->repo->add($type, $name, filesize($path), $user);
    }

    public function history(): array
    {
        return $this->repo->all();
    }

    public function file(int $id): ?array
    {
        $row = $this->repo->find($id);
        if (! $row) {
            return null;
        }$path = realpath($this->dir.'/'.$row->file_name);
        $base = realpath($this->dir);

        return $path && $base && str_starts_with($path, $base) && is_file($path) ? ['row' => $row, 'path' => $path] : null;
    }

    public function delete(int $id): bool
    {
        $file = $this->file($id);
        if (! $file) {
            return false;
        }if (! @unlink($file['path'])) {
            return false;
        }

        return $this->repo->delete($id);
    }

    private function addDirectory(ZipArchive $zip, string $root, string $prefix): void
    {
        if (! is_dir($root)) {
            return;
        }$base = strlen(rtrim($root, '/\\')) + 1;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $f) {
            if ($f->isFile()) {
                $zip->addFile($f->getPathname(), $prefix.'/'.str_replace('\\', '/', substr($f->getPathname(), $base)));
            }
        }
    }
}
