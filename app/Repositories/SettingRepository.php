<?php

namespace App\Repositories;

use App\Models\Database;

class SettingRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function all(): array
    {
        $this->db->query('SELECT khoa_cai_dat,gia_tri_cai_dat FROM cai_dat');
        $rows = $this->db->resultSet();
        $out = [];
        foreach ($rows as $r) {
            $out[$r->khoa_cai_dat] = $r->gia_tri_cai_dat;
        }

        return $out;
    }

    public function save(array $data): bool
    {
        foreach ($data as $k => $v) {
            $this->db->query('INSERT INTO cai_dat(khoa_cai_dat,gia_tri_cai_dat) VALUES(:k,:v) ON DUPLICATE KEY UPDATE gia_tri_cai_dat=VALUES(gia_tri_cai_dat)');
            $this->db->bind(':k', $k);
            $this->db->bind(':v', (string) $v);
            if (! $this->db->execute()) {
                return false;
            }
        }

        return true;
    }
}
