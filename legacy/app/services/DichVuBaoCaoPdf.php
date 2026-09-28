<?php

use Dompdf\Dompdf;
use Dompdf\Options;

class ReportPdfService
{
    private const LABELS = [
        'total_users'=>'Tổng số người dùng','new_users'=>'Người dùng mới','active_users'=>'Người dùng đang hoạt động',
        'locked_users'=>'Người dùng bị khóa','total_posts'=>'Tổng số tin đăng','new_posts'=>'Tin đăng mới',
        'vip_posts'=>'Tin đăng VIP','pending_posts'=>'Tin đăng chờ duyệt','rejected_posts'=>'Tin đăng bị từ chối',
        'expired_posts'=>'Tin đăng hết hạn','views'=>'Tổng lượt xem','transactions'=>'Tổng số giao dịch',
        'transaction_value'=>'Tổng giá trị giao dịch','success_tx'=>'Giao dịch thành công','failed_tx'=>'Giao dịch thất bại',
        'revenue'=>'Doanh thu','calls'=>'Lượt gọi','chats'=>'Lượt trò chuyện','saves'=>'Lượt lưu tin',
    ];

    private const MONEY = ['transaction_value', 'revenue'];

    public function download(array $report, string $exporter): never
    {
        $autoload = dirname(APP_ROOT) . '/vendor/autoload.php';
        if (!is_file($autoload)) {
            http_response_code(500);
            exit('Máy chủ chưa cài đặt thư viện xuất PDF.');
        }
        require_once $autoload;

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($this->document($report, $exporter), 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $canvas->page_text(500, 812, 'Trang {PAGE_NUM}/{PAGE_COUNT}', $font, 8, [0.3, 0.3, 0.3]);

        $name = 'bao_cao_trinh_ky_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($name, ['Attachment' => true]);
        exit;
    }

    private function document(array $report, string $exporter): string
    {
        $exporter = 'Minh Chí';
        $f = $report['filters'];
        $from = $this->date((string)$f['from']);
        $to = $this->date((string)$f['to']);
        $region = trim((string)$f['region']) ?: 'Tất cả';
        $type = trim((string)$f['type']) ?: 'Tất cả';
        $number = 'BC-QT/' . date('Ymd-His');
        $generated = date('d/m/Y H:i:s');
        $signature = $this->signatureDataUri();

        $overviewRows = '';
        $index = 1;
        foreach (($report['overview'] ?? []) as $key => $value) {
            $display = in_array($key, self::MONEY, true)
                ? number_format((float)$value, 0, ',', '.') . ' VND'
                : number_format((float)$value, 0, ',', '.');
            $overviewRows .= '<tr><td class="center">' . $index++ . '</td><td>' . $this->e(self::LABELS[$key] ?? ucfirst(str_replace('_', ' ', $key))) . '</td><td class="right">' . $display . '</td></tr>';
        }

        $dailyRows = '';
        $totalTransactions = 0;
        $totalRevenue = 0;
        foreach (($report['chart'] ?? []) as $row) {
            $totalTransactions += (int)$row->transactions;
            $totalRevenue += (float)$row->revenue;
            $dailyRows .= '<tr><td class="center">' . $this->date((string)$row->label) . '</td><td class="right">' . number_format((int)$row->transactions, 0, ',', '.') . '</td><td class="right">' . number_format((float)$row->revenue, 0, ',', '.') . '</td></tr>';
        }
        if ($dailyRows === '') $dailyRows = '<tr><td colspan="3" class="center muted">Không có dữ liệu trong kỳ báo cáo.</td></tr>';
        else $dailyRows .= '<tr class="total"><td>Tổng cộng</td><td class="right">' . number_format($totalTransactions, 0, ',', '.') . '</td><td class="right">' . number_format($totalRevenue, 0, ',', '.') . '</td></tr>';

        $sourceRows = '';
        foreach (($report['sources'] ?? []) as $row) {
            $sourceRows .= '<tr><td>' . $this->e((string)$row->source) . '</td><td class="right">' . number_format((int)$row->quantity, 0, ',', '.') . '</td><td class="right">' . number_format((float)$row->amount, 0, ',', '.') . '</td></tr>';
        }
        if ($sourceRows === '') $sourceRows = '<tr><td colspan="3" class="center muted">Không phát sinh dữ liệu dịch vụ.</td></tr>';

        return '<!doctype html><html lang="vi"><head><meta charset="UTF-8"><style>' . $this->css() . '</style></head><body>
        <table class="heading"><tr><td><b>TIMNHADAT.SITE</b><br><span>BỘ PHẬN QUẢN TRỊ HỆ THỐNG</span><br><span>Số: ' . $number . '</span></td><td class="national"><b>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</b><br><b>Độc lập - Tự do - Hạnh phúc</b><div class="rule"></div><i>Ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y') . '</i></td></tr></table>
        <h1>BÁO CÁO HOẠT ĐỘNG HỆ THỐNG</h1><div class="subtitle">Kỳ báo cáo: từ ngày ' . $from . ' đến ngày ' . $to . '</div>
        <table class="meta"><tr><td><b>Khu vực:</b> ' . $this->e($region) . '</td><td><b>Loại bất động sản:</b> ' . $this->e($type) . '</td></tr><tr><td><b>Người lập báo cáo:</b> ' . $this->e($exporter) . '</td><td><b>Thời gian lập:</b> ' . $generated . '</td></tr></table>
        <h2>I. SỐ LIỆU TỔNG HỢP</h2><table><thead><tr><th style="width:8%">STT</th><th>Chỉ tiêu</th><th style="width:28%">Giá trị</th></tr></thead><tbody>' . $overviewRows . '</tbody></table>
        <h2>II. DOANH THU VÀ GIAO DỊCH THEO NGÀY</h2><table><thead><tr><th>Ngày</th><th>Số giao dịch</th><th>Doanh thu (VND)</th></tr></thead><tbody>' . $dailyRows . '</tbody></table>
        <h2>III. CƠ CẤU NGUỒN DỊCH VỤ</h2><table><thead><tr><th>Loại dịch vụ</th><th>Số lượng</th><th>Giá trị (VND)</th></tr></thead><tbody>' . $sourceRows . '</tbody></table>
        <h2>IV. NHẬN XÉT, KIẾN NGHỊ</h2><div class="notes">................................................................................................................................................<br>................................................................................................................................................<br>................................................................................................................................................</div>
        <p class="closing">Báo cáo được lập từ dữ liệu ghi nhận trên hệ thống TimNhaDat.site. Kính trình cấp có thẩm quyền xem xét và phê duyệt.</p>
        <table class="signatures"><tr><td><b>NGƯỜI LẬP BIỂU</b><br><i>(Ký, ghi rõ họ tên)</i><div class="sign-space signed">' . ($signature !== '' ? '<img src="' . $signature . '" alt="Chữ ký Minh Chí">' : '') . '</div><b>' . $this->e($exporter) . '</b></td><td><b>TRƯỞNG BỘ PHẬN</b><br><i>(Ký, ghi rõ họ tên)</i><div class="sign-space"></div></td><td><b>NGƯỜI PHÊ DUYỆT</b><br><i>(Ký, đóng dấu, ghi rõ họ tên)</i><div class="sign-space"></div></td></tr></table>
        </body></html>';
    }

    private function css(): string
    {
        return '@page{margin:20mm 16mm 18mm}body{font-family:"DejaVu Sans",sans-serif;font-size:10.5pt;color:#111;line-height:1.35}.heading{border:0;margin-bottom:18px}.heading td{border:0;text-align:center;vertical-align:top;width:50%;font-size:9.5pt}.heading .national{font-size:9pt}.rule{border-top:1px solid #111;width:42%;margin:4px auto 6px}h1{text-align:center;font-size:16pt;margin:0 0 3px}.subtitle{text-align:center;font-style:italic;margin-bottom:14px}.meta{margin-bottom:13px}.meta td{width:50%;padding:5px 7px}h2{font-size:11pt;margin:14px 0 6px;page-break-after:avoid}table{width:100%;border-collapse:collapse}th,td{border:1px solid #333;padding:5px 6px;vertical-align:middle}th{background:#e8edf3;text-align:center;font-weight:bold}.center{text-align:center}.right{text-align:right}.muted{color:#666;font-style:italic}.total{font-weight:bold;background:#f1f3f5}.notes{line-height:2;margin:5px 0 12px}.closing{text-align:justify;margin:8px 0 15px}.signatures{page-break-inside:avoid;margin-top:8px}.signatures td{border:0;text-align:center;width:33.33%;vertical-align:top;font-size:9.5pt}.sign-space{height:65px}.signed{height:65px;line-height:65px}.signed img{display:inline-block;width:150px;height:58px;object-fit:contain;vertical-align:middle}.signatures i{font-size:8.5pt}';
    }

    private function signatureDataUri(): string
    {
        $path = dirname(APP_ROOT) . '/public/images/signatures/minh-chi-signature.png';
        if (!is_file($path) || !is_readable($path)) return '';
        $data = file_get_contents($path);
        return $data === false ? '' : 'data:image/png;base64,' . base64_encode($data);
    }

    private function date(string $value): string
    {
        $time = strtotime($value);
        return $time ? date('d/m/Y', $time) : $value;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
