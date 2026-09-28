<?php

class AdminDashboardController extends Controller
{
    private AdminDashboardService $service;

    public function __construct()
    {
        Auth::requireRole(1);
        $this->service = new AdminDashboardService();
    }

    public function index(): void
    {
        $overview=$this->service->overview((int)($_GET['period']??30));
        $s=$overview['statistics']; $top=$overview['top'];
        $objects=static fn(array $rows):array=>array_map(static fn($row):object=>(object)$row,$rows);
        $chart=$overview['chart'];
        $data=['title'=>'Admin Dashboard - '.SITE_NAME,'total_projects'=>(int)($s['posts']['total']??0),
            'active_projects'=>(int)($s['posts']['total']??0)-(int)($s['posts']['pending']??0),
            'total_users'=>(int)($s['users']['total']??0),'total_leads'=>(int)($s['users']['today']??0),
            'chart_categories'=>array_map(static fn($r):object=>(object)['danh_muc'=>$r->loai_bat_dong_san??'Khác','so_luong'=>(int)($r->total_posts??0)],$objects($top['types'])),
            'chart_months'=>array_map(static fn($r):object=>(object)['thang'=>date('d/m',strtotime($r->day)),'tin_dang_moi'=>(int)$r->posts,'du_an_moi'=>(int)$r->users],$objects($chart)),
            'pending_posts'=>$objects($overview['pending']),
            'top_posts'=>$objects($top['posts']),'top_authors'=>$objects($top['authors']),'top_regions'=>$objects($top['regions']),'top_types'=>$objects($top['types']),
            'revenue_chart'=>array_map(static fn($r):object=>(object)['period'=>date('d/m',strtotime($r->day)),'total_revenue'=>(int)$r->revenue],$objects($chart)),
            'interactions_chart'=>array_map(static fn($r):object=>(object)['period'=>date('d/m',strtotime($r->day)),'total_views'=>(int)$r->views,'total_chats'=>(int)$r->chats,'total_calls'=>0],$objects($chart)),
            'admin_overview'=>$overview];
        $this->view('admin/dashboard',$data);
    }

    public function statistics(): void { $this->json($this->service->statistics()); }
    public function chart(): void { $this->json($this->service->chart((int)($_GET['period']??30))); }
    public function realtime(): void { $this->json($this->service->realtime()); }
    public function system(): void { $this->json($this->service->system()); }

    private function json(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: private, no-store');
        echo json_encode(['success'=>true,'data'=>$data],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
}
