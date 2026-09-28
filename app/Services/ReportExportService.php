<?php

namespace App\Services;

class ReportExportService
{
    public function download(array $report, string $format, string $exporter): never
    {
        $format = in_array($format, ['csv', 'excel'], true) ? $format : 'csv';
        $name = 'bao_cao_'.date('Ymd_His');
        header('Content-Type: '.($format === 'excel' ? 'application/vnd.ms-excel' : 'text/csv').'; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$name.'.'.($format === 'excel' ? 'xls' : 'csv'));
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, ['TIMNHADAT.SITE - BÁO CÁO QUẢN TRỊ']);
        fputcsv($out, ['Người xuất', $exporter]);
        fputcsv($out, ['Thời gian', date('d/m/Y H:i:s')]);
        fputcsv($out, ['Khoảng', $report['filters']['from'].' - '.$report['filters']['to']]);
        fputcsv($out, []);
        fputcsv($out, ['Chỉ số', 'Giá trị']);
        foreach ($report['overview'] as $k => $v) {
            fputcsv($out, [$k, $v]);
        } fclose($out);
        exit;
    }
}
