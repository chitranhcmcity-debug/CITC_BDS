<?php

class ReportExportService
{
    private const LABELS = [
        'total_users'       => 'Tổng số người dùng',
        'new_users'         => 'Người dùng mới',
        'active_users'      => 'Người dùng đang hoạt động',
        'locked_users'      => 'Người dùng bị khóa',
        'total_posts'       => 'Tổng số tin đăng',
        'new_posts'         => 'Tin đăng mới',
        'vip_posts'         => 'Tin đăng VIP',
        'pending_posts'     => 'Tin đăng chờ duyệt',
        'rejected_posts'    => 'Tin đăng bị từ chối',
        'expired_posts'     => 'Tin đăng hết hạn',
        'views'             => 'Tổng lượt xem',
        'transactions'      => 'Tổng số giao dịch',
        'transaction_value' => 'Tổng giá trị giao dịch',
        'success_tx'        => 'Giao dịch thành công',
        'failed_tx'         => 'Giao dịch thất bại',
        'revenue'           => 'Doanh thu',
        'calls'             => 'Lượt gọi',
        'chats'             => 'Lượt trò chuyện',
        'saves'             => 'Lượt lưu tin',
    ];

    private const CURRENCY_KEYS = ['transaction_value', 'revenue'];

    public function download(array $report, string $format, string $exporter): never
    {
        if ($format === 'excel') {
            $this->downloadExcel($report, $exporter);
        }

        $this->downloadCsv($report, $exporter);
    }

    private function downloadCsv(array $report, string $exporter): never
    {
        $name = 'bao_cao_quan_tri_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('X-Content-Type-Options: nosniff');

        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'wb');
        fputcsv($out, ['TIMNHADAT.SITE - BÁO CÁO QUẢN TRỊ']);
        fputcsv($out, ['Người xuất', $exporter]);
        fputcsv($out, ['Thời gian xuất', date('d/m/Y H:i:s')]);
        fputcsv($out, ['Kỳ báo cáo', $this->period($report)]);
        fputcsv($out, ['Khu vực', (string)($report['filters']['region'] ?: 'Tất cả')]);
        fputcsv($out, ['Loại bất động sản', (string)($report['filters']['type'] ?: 'Tất cả')]);

        foreach ($this->sections($report) as $section) {
            fputcsv($out, []);
            fputcsv($out, [$section['title']]);
            fputcsv($out, $section['headers']);
            foreach ($section['rows'] as $row) {
                fputcsv($out, $row);
            }
        }

        fclose($out);
        exit;
    }

    private function downloadExcel(array $report, string $exporter): never
    {
        if (!class_exists(ZipArchive::class)) {
            http_response_code(500);
            exit('Máy chủ chưa hỗ trợ tạo tệp Excel XLSX.');
        }

        $temp = tempnam(sys_get_temp_dir(), 'report_xlsx_');
        $zip = new ZipArchive();
        if ($temp === false || $zip->open($temp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            http_response_code(500);
            exit('Không thể khởi tạo tệp Excel.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRelationships());
        $zip->addFromString('docProps/app.xml', $this->appProperties());
        $zip->addFromString('docProps/core.xml', $this->coreProperties());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationships());
        $zip->addFromString('xl/styles.xml', $this->formalStyles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheet($report, $exporter));
        $zip->close();

        $name = 'bao_cao_quan_tri_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . filesize($temp));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('X-Content-Type-Options: nosniff');
        readfile($temp);
        @unlink($temp);
        exit;
    }

    private function worksheet(array $report, string $exporter): string
    {
        $exporter = 'Minh Chí';
        $reportNumber = 'BC-QT/' . date('Ymd-His');
        $today = 'Ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y');
        $xmlRows = [
            $this->groupedTextRow(1, [['A','TIMNHADAT.SITE',14],['D','CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM',14]], 21),
            $this->groupedTextRow(2, [['A','BỘ PHẬN QUẢN TRỊ HỆ THỐNG',10],['D','Độc lập - Tự do - Hạnh phúc',10]], 20),
            $this->groupedTextRow(3, [['A','Số: ' . $reportNumber,7],['D',$today,9]], 19),
            $this->mergedTextRow(5, 'A', 'G', 'BÁO CÁO HOẠT ĐỘNG HỆ THỐNG', 1, 32),
            $this->mergedTextRow(6, 'A', 'G', 'Kỳ báo cáo: ' . $this->period($report), 9, 21),
            $this->textRow(8, 'Người lập báo cáo', $exporter),
            $this->textRow(9, 'Thời gian lập', date('d/m/Y H:i:s')),
            $this->textRow(10, 'Khu vực', (string)($report['filters']['region'] ?: 'Tất cả')),
            $this->textRow(11, 'Loại bất động sản', (string)($report['filters']['type'] ?: 'Tất cả')),
        ];
        $rowNumber = 13;
        $merges = ['A1:C1','D1:G1','A2:C2','D2:G2','A3:C3','D3:G3','A5:G5','A6:G6'];
        foreach ($this->sections($report) as $section) {
            $xmlRows[] = '<row r="' . $rowNumber . '" ht="23" customHeight="1"><c r="A' . $rowNumber . '" s="8" t="inlineStr"><is><t>' . $this->xml($section['title']) . '</t></is></c></row>';
            $merges[] = 'A' . $rowNumber . ':G' . $rowNumber;
            $rowNumber++;
            $currencyColumns = [];
            foreach ($section['headers'] as $column => $header) if (str_contains((string)$header, '(VND)')) $currencyColumns[] = $column;
            $xmlRows[] = $this->dataRow($rowNumber++, $section['headers'], true);
            foreach ($section['rows'] as $row) {
                $xmlRows[] = $this->dataRow($rowNumber++, $row, false, $currencyColumns);
            }
            $rowNumber++;
        }

        $xmlRows[] = $this->mergedTextRow($rowNumber, 'A', 'G', 'NHẬN XÉT, KIẾN NGHỊ', 8, 23);
        $merges[] = 'A' . $rowNumber . ':G' . $rowNumber;
        $rowNumber++;
        for ($i = 0; $i < 3; $i++, $rowNumber++) {
            $xmlRows[] = $this->mergedTextRow($rowNumber, 'A', 'G', '........................................................................................................................................................................', 7, 22);
            $merges[] = 'A' . $rowNumber . ':G' . $rowNumber;
        }
        $rowNumber++;
        $xmlRows[] = $this->mergedTextRow($rowNumber, 'A', 'G', 'Báo cáo được lập từ dữ liệu ghi nhận trên hệ thống TimNhaDat.site. Kính trình cấp có thẩm quyền xem xét và phê duyệt.', 7);
        $merges[] = 'A' . $rowNumber . ':G' . $rowNumber;
        $rowNumber += 2;

        $signatureRow = $rowNumber;
        $signatureGroups = [['A','B','NGƯỜI LẬP BIỂU'],['C','E','TRƯỞNG BỘ PHẬN'],['F','G','NGƯỜI PHÊ DUYỆT']];
        $xmlRows[] = $this->groupedTextRow($signatureRow, array_map(static fn($item) => [$item[0], $item[2], 10], $signatureGroups), 22);
        $xmlRows[] = $this->groupedTextRow($signatureRow + 1, array_map(static fn($item) => [$item[0], '(Ký, ghi rõ họ tên)', 9], $signatureGroups), 20);
        foreach ($signatureGroups as [$from,$to,$label]) {
            $merges[] = $from . $signatureRow . ':' . $to . $signatureRow;
            $merges[] = $from . ($signatureRow + 1) . ':' . $to . ($signatureRow + 1);
        }
        $xmlRows[] = '<row r="' . ($signatureRow + 2) . '" ht="55" customHeight="1"/>';
        $xmlRows[] = $this->mergedTextRow($signatureRow + 3, 'A', 'B', $exporter, 10);
        $merges[] = 'A' . ($signatureRow + 3) . ':B' . ($signatureRow + 3);

        $mergeXml = implode('', array_map(static fn($ref) => '<mergeCell ref="' . $ref . '"/>', $merges));

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="11" topLeftCell="A12" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="19"/><cols><col min="1" max="1" width="16" customWidth="1"/><col min="2" max="2" width="30" customWidth="1"/><col min="3" max="3" width="40" customWidth="1"/><col min="4" max="5" width="20" customWidth="1"/><col min="6" max="7" width="18" customWidth="1"/></cols>'
            . '<sheetData>' . implode('', $xmlRows) . '</sheetData>'
            . '<mergeCells count="' . count($merges) . '">' . $mergeXml . '</mergeCells>'
            . '<printOptions horizontalCentered="1"/><pageMargins left="0.35" right="0.35" top="0.5" bottom="0.55" header="0.2" footer="0.25"/>'
            . '<headerFooter><oddFooter>&amp;LTimNhaDat.site&amp;C-Báo cáo nội bộ-&amp;RTrang &amp;P/&amp;N</oddFooter></headerFooter>'
            . '<pageSetup orientation="landscape" fitToWidth="1" fitToHeight="0" paperSize="9"/>'
            . '</worksheet>';
    }

    private function mergedTextRow(int $row, string $from, string $to, string $value, int $style = 7, int $height = 18): string
    {
        return '<row r="' . $row . '" ht="' . $height . '" customHeight="1"><c r="' . $from . $row . '" s="' . $style . '" t="inlineStr"><is><t>'
            . $this->xml($value) . '</t></is></c></row>';
    }

    private function groupedTextRow(int $row, array $cells, int $height = 18): string
    {
        $xml = '';
        foreach ($cells as [$column, $value, $style]) {
            $xml .= '<c r="' . $column . $row . '" s="' . $style . '" t="inlineStr"><is><t>' . $this->xml((string)$value) . '</t></is></c>';
        }
        return '<row r="' . $row . '" ht="' . $height . '" customHeight="1">' . $xml . '</row>';
    }

    private function dataRow(int $number, array $values, bool $header = false, array $currencyColumns = []): string
    {
        $cells = '';
        foreach (array_values($values) as $index => $value) {
            $column = chr(65 + $index);
            if (is_int($value) || is_float($value) || (is_numeric($value) && $value !== '')) {
                $style = $header ? 2 : (in_array($index, $currencyColumns, true) ? 4 : 3);
                $cells .= '<c r="' . $column . $number . '" s="' . $style . '"><v>' . (float)$value . '</v></c>';
            } else {
                $cells .= '<c r="' . $column . $number . '" s="' . ($header ? 2 : 5) . '" t="inlineStr"><is><t>' . $this->xml((string)$value) . '</t></is></c>';
            }
        }
        return '<row r="' . $number . '">' . $cells . '</row>';
    }

    private function textRow(int $row, string $label, string $value): string
    {
        return '<row r="' . $row . '"><c r="A' . $row . '" s="6" t="inlineStr"><is><t>'
            . $this->xml($label) . '</t></is></c><c r="B' . $row . '" s="7" t="inlineStr"><is><t>'
            . $this->xml($value) . '</t></is></c></row>';
    }

    private function period(array $report): string
    {
        $from = (string)($report['filters']['from'] ?? '');
        $to = (string)($report['filters']['to'] ?? '');
        $format = static function (string $date): string {
            $time = strtotime($date);
            return $time ? date('d/m/Y', $time) : $date;
        };
        return $format($from) . ' - ' . $format($to);
    }

    private function sections(array $report): array
    {
        $overview = [];
        foreach (($report['overview'] ?? []) as $key => $value) {
            $overview[] = [self::LABELS[$key] ?? $this->humanize((string)$key), (float)($value ?? 0)];
        }
        $chart = array_map(static fn($r) => [date('d/m/Y', strtotime((string)$r->label)), (int)$r->transactions, (float)$r->revenue], $report['chart'] ?? []);
        $sources = array_map(fn($r) => [$this->plain((string)$r->source), (int)$r->quantity, (float)$r->amount], $report['sources'] ?? []);
        $users = array_map(fn($r) => [(int)$r->id, $this->plain((string)$r->ten), (string)$r->email, (int)$r->posts, (float)$r->deposited], $report['topUsers'] ?? []);
        $posts = array_map(fn($r) => [(int)$r->id, $this->plain((string)$r->tieu_de), (int)$r->luot_xem, (int)$r->luot_luu, (int)$r->luot_click_sdt, (int)$r->goi_vip, $this->plain((string)$r->trang_thai)], $report['topPosts'] ?? []);
        $transactions = array_map(fn($r) => [(int)$r->id, $this->plain((string)($r->ten ?? 'Không xác định')), (float)$r->so_tien, $this->plain((string)$r->phuong_thuc), $this->plain((string)$r->trang_thai), date('d/m/Y H:i:s', strtotime((string)$r->ngay_tao))], $report['transactions'] ?? []);
        $chat = [];
        $chatLabels = ['conversations'=>'Tổng hội thoại live chat','closed_conversations'=>'Hội thoại đã đóng','avg_minutes'=>'Thời gian xử lý trung bình (phút)'];
        foreach (($report['chat'] ?? []) as $key => $value) $chat[] = [$chatLabels[$key] ?? $this->humanize((string)$key), (float)($value ?? 0)];

        return [
            ['title'=>'1. TỔNG QUAN HỆ THỐNG','headers'=>['Chỉ số','Giá trị'],'rows'=>$overview],
            ['title'=>'2. DOANH THU THEO NGÀY','headers'=>['Ngày','Số giao dịch','Doanh thu (VND)'],'rows'=>$chart],
            ['title'=>'3. NGUỒN DỊCH VỤ','headers'=>['Loại dịch vụ','Số lượng','Giá trị (VND)'],'rows'=>$sources],
            ['title'=>'4. TOP NGƯỜI DÙNG','headers'=>['ID','Họ và tên','Email','Số tin đăng','Tổng nạp (VND)'],'rows'=>$users],
            ['title'=>'5. TOP TIN ĐĂNG','headers'=>['ID','Tiêu đề','Lượt xem','Lượt lưu','Click SĐT','Gói VIP','Trạng thái'],'rows'=>$posts],
            ['title'=>'6. GIAO DỊCH GẦN ĐÂY','headers'=>['Mã GD','Người dùng','Số tiền (VND)','Phương thức','Trạng thái','Ngày tạo'],'rows'=>$transactions],
            ['title'=>'7. LIVE CHAT','headers'=>['Chỉ số','Giá trị'],'rows'=>$chat],
        ];
    }

    private function plain(string $value): string
    {
        return $value;
    }

    private function humanize(string $key): string
    {
        return ucfirst(str_replace('_', ' ', $key));
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/><Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/></Types>';
    }

    private function rootRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/></Relationships>';
    }

    private function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><bookViews><workbookView xWindow="0" yWindow="0" windowWidth="24000" windowHeight="12000"/></bookViews><sheets><sheet name="Báo cáo tổng hợp" sheetId="1" r:id="rId1"/></sheets><calcPr calcId="191029"/></workbook>';
    }

    private function workbookRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><numFmts count="2"><numFmt numFmtId="164" formatCode="#,##0"/><numFmt numFmtId="165" formatCode="#,##0 &quot;₫&quot;"/></numFmts><fonts count="3"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="16"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF146C43"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF198754"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFD9E2E8"/></left><right style="thin"><color rgb="FFD9E2E8"/></right><top style="thin"><color rgb="FFD9E2E8"/></top><bottom style="thin"><color rgb="FFD9E2E8"/></bottom><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="8"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf><xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center"/></xf><xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right"/></xf><xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment horizontal="left"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    private function formalStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="2"><numFmt numFmtId="164" formatCode="#,##0"/><numFmt numFmtId="165" formatCode="#,##0 &quot;VND&quot;"/></numFmts>'
            . '<fonts count="6">'
            . '<font><sz val="11"/><name val="Times New Roman"/></font>'
            . '<font><b/><sz val="16"/><name val="Times New Roman"/></font>'
            . '<font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Times New Roman"/></font>'
            . '<font><b/><sz val="11"/><name val="Times New Roman"/></font>'
            . '<font><i/><sz val="10"/><name val="Times New Roman"/></font>'
            . '<font><b/><sz val="12"/><name val="Times New Roman"/></font>'
            . '</fonts>'
            . '<fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF1F4E78"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFD9EAF7"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FF666666"/></left><right style="thin"><color rgb="FF666666"/></right><top style="thin"><color rgb="FF666666"/></top><bottom style="thin"><color rgb="FF666666"/></bottom><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="15">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="164" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyNumberFormat="1"/>'
            . '<xf numFmtId="0" fontId="5" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function appProperties(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><Application>TimNhaDat.site</Application></Properties>';
    }

    private function coreProperties(): string
    {
        $now = gmdate('Y-m-d\TH:i:s\Z');
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:title>Báo cáo quản trị TimNhaDat.site</dc:title><dc:creator>TimNhaDat.site</dc:creator><dcterms:created xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:created></cp:coreProperties>';
    }
}
