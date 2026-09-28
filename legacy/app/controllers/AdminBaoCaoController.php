<?php
class AdminBaoCaoController extends Controller
{
    private AdminReportService $service;
    public function __construct(){ Auth::requireRole(1); $this->service=new AdminReportService(); }
    public function index(): void { $this->render('dashboard'); }
    public function dashboard(): void { $this->render('dashboard'); }
    public function doanhThu(): void { $this->render('revenue'); }
    public function nguoiDung(): void { $this->render('users'); }
    public function tinDang(): void { $this->render('posts'); }
    public function giaoDich(): void { $this->render('transactions'); }
    public function chat(): void { $this->render('chat'); }
    public function export(): void { $report=$this->service->build('dashboard',$_GET); $format=(string)($_GET['format']??'csv'); $exporter=(string)(Session::get('user_name')?:Session::get('admin_name')?:'Admin'); if($format==='pdf'){(new ReportPdfService())->download($report,$exporter);} (new ReportExportService())->download($report,$format,$exporter); }
    public function exportCsv(): void { $_GET['format']='csv'; $this->export(); }
    private function render(string $section): void { $data=$this->service->build($section,$_GET); $data['title']='Báo cáo & Thống kê - '.SITE_NAME; SystemLogger::admin('view','bao_cao','report',null,'Xem báo cáo '.$section,[],['filters'=>$data['filters']],(int)Session::get('user_id')); $this->view('admin/report/dashboard',$data); }
}
