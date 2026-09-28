<?php

namespace App\Services;

/**
 * ExportService – Xuất dữ liệu danh mục và khu vực hành chính ra file CSV/Excel.
 * Tuân thủ SOLID, Service Pattern.
 */
class ExportService
{
    /**
     * Xuất dữ liệu ra file CSV gửi trực tiếp về client.
     */
    public function exportCSV(string $filename, array $headers, array $rows): void
    {
        // Thiết lập headers tải xuống tệp tin
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Ghi UTF-8 BOM cho Excel đọc tiếng Việt không bị lỗi font
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Ghi tiêu đề cột
        fputcsv($output, $headers);

        // Ghi nội dung dòng
        foreach ($rows as $row) {
            fputcsv($output, (array) $row);
        }

        fclose($output);
        exit();
    }
}
