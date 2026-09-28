<?php

namespace App\Services;

use App\Models\Database;

/**
 * ImportService – Nhập dữ liệu hành chính (Tỉnh/Quận/Phường) và danh mục từ tệp CSV/Excel XML.
 * Kiểm tra dữ liệu trùng, báo lỗi chi tiết.
 * Tuân thủ SOLID, Service Pattern.
 */
class ImportService
{
    private LocationService $locSvc;

    private CategoryService $catSvc;

    public function __construct()
    {
        $this->locSvc = new LocationService;
        $this->catSvc = new CategoryService;
    }

    /**
     * Nhập dữ liệu hành chính từ tệp CSV.
     * Hỗ trợ định dạng:
     * - provinces: code, name, type
     * - districts: code, province_code, name, type
     * - wards: code, district_code, name, type
     */
    public function importLocations(string $type, string $filePath, int $adminId): array
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            return ['success' => false, 'message' => 'Tệp tin không tồn tại hoặc không thể đọc.'];
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return ['success' => false, 'message' => 'Không thể mở tệp tin.'];
        }

        // Bỏ qua BOM nếu có
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $imported = 0;
        $errors = [];
        $lineNum = 0;

        $db = new Database;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $lineNum++;
            if ($lineNum === 1) {
                continue;
            } // Bỏ qua tiêu đề (Header)

            // Làm sạch dữ liệu
            $row = array_map('trim', $row);
            if (empty($row) || count($row) < 3) {
                $errors[] = "Dòng {$lineNum}: Dữ liệu không đầy đủ.";

                continue;
            }

            try {
                if ($type === 'provinces') {
                    $code = $row[0];
                    $name = $row[1];
                    $pType = $row[2] ?? 'Tỉnh';

                    // Kiểm tra trùng
                    $db->query('SELECT id FROM provinces WHERE code = :code');
                    $db->bind(':code', $code);
                    if ($db->single()) {
                        $errors[] = "Dòng {$lineNum}: Mã tỉnh '{$code}' đã tồn tại.";

                        continue;
                    }

                    $this->locSvc->createProvince([
                        'code' => $code,
                        'name' => $name,
                        'type' => $pType,
                    ], $adminId);
                    $imported++;

                } elseif ($type === 'districts') {
                    $code = $row[0];
                    $provinceCode = $row[1];
                    $name = $row[2];
                    $dType = $row[3] ?? 'Quận';

                    // Kiểm tra tỉnh cha tồn tại
                    $db->query('SELECT id FROM provinces WHERE code = :pcode');
                    $db->bind(':pcode', $provinceCode);
                    if (! $db->single()) {
                        $errors[] = "Dòng {$lineNum}: Mã tỉnh cha '{$provinceCode}' không tồn tại.";

                        continue;
                    }

                    // Kiểm tra trùng
                    $db->query('SELECT id FROM districts WHERE code = :code');
                    $db->bind(':code', $code);
                    if ($db->single()) {
                        $errors[] = "Dòng {$lineNum}: Mã quận/huyện '{$code}' đã tồn tại.";

                        continue;
                    }

                    $this->locSvc->createDistrict([
                        'code' => $code,
                        'province_code' => $provinceCode,
                        'name' => $name,
                        'type' => $dType,
                    ], $adminId);
                    $imported++;

                } elseif ($type === 'wards') {
                    $code = $row[0];
                    $districtCode = $row[1];
                    $name = $row[2];
                    $wType = $row[3] ?? 'Phường';

                    // Kiểm tra quận cha tồn tại
                    $db->query('SELECT id FROM districts WHERE code = :dcode');
                    $db->bind(':dcode', $districtCode);
                    if (! $db->single()) {
                        $errors[] = "Dòng {$lineNum}: Mã quận cha '{$districtCode}' không tồn tại.";

                        continue;
                    }

                    // Kiểm tra trùng
                    $db->query('SELECT id FROM wards WHERE code = :code');
                    $db->bind(':code', $code);
                    if ($db->single()) {
                        $errors[] = "Dòng {$lineNum}: Mã phường/xã '{$code}' đã tồn tại.";

                        continue;
                    }

                    $this->locSvc->createWard([
                        'code' => $code,
                        'district_code' => $districtCode,
                        'name' => $name,
                        'type' => $wType,
                    ], $adminId);
                    $imported++;
                }
            } catch (Throwable $e) {
                $errors[] = "Dòng {$lineNum}: Lỗi hệ thống – ".$e->getMessage();
            }
        }

        fclose($handle);

        return [
            'success' => count($errors) === 0 || $imported > 0,
            'imported' => $imported,
            'errors' => $errors,
            'message' => "Đã nhập thành công {$imported} dòng dữ liệu.",
        ];
    }
}
