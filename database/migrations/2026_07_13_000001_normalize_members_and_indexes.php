<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Guest là vai trò cho khách chưa đăng nhập, không phải tài khoản đã lưu.
        DB::table('nguoi_dung')->where('ma_vai_tro', 7)->update(['ma_vai_tro' => 3]);

        $duplicates = [
            ['du_an', 'idx_duan_user_status', 'idx_dashboard_user_status_created'],
            ['hinh_anh_du_an', 'ma_du_an', 'idx_hinh_anh_ma_duan'],
            ['nguoi_dung', 'idx_nd_email', 'email'],
            ['nguoi_dung', 'idx_nd_trang_thai', 'idx_nd_status'],
        ];

        foreach ($duplicates as [$table, $redundant, $replacement]) {
            if ($this->hasIndex($table, $redundant) && $this->hasIndex($table, $replacement)) {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$redundant}`");
            }
        }
    }

    public function down(): void
    {
        // Không đổi tài khoản thành guest trở lại vì sẽ làm mất quyền thành viên.
        $indexes = [
            ['du_an', 'idx_duan_user_status', '`ma_nguoi_dung`, `trang_thai`, `ngay_tao`'],
            ['hinh_anh_du_an', 'ma_du_an', '`ma_du_an`'],
            ['nguoi_dung', 'idx_nd_email', '`email`'],
            ['nguoi_dung', 'idx_nd_trang_thai', '`trang_thai`'],
        ];

        foreach ($indexes as [$table, $name, $columns]) {
            if (! $this->hasIndex($table, $name)) {
                DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$name}` ({$columns})");
            }
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
