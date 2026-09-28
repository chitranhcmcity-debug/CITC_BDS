<?php

/** Admin-only UI and CSV API for structured system logs. */
class AdminSystemLogController extends Controller
{
    private SystemLogService $service;
    private SystemLogPolicy $policy;

    public function __construct()
    {
        Auth::requireLogin();
        $this->service=new SystemLogService();
        $this->policy=new SystemLogPolicy((int)Session::get('user_role_id'));
        if(!$this->policy->canAny())$this->deny();
    }

    public function index(): void
    {
        $this->view('admin/system-log/index',['title'=>'Nhật Ký Hệ Thống','logs'=>$this->service->dashboard()]);
    }

    public function activity(): void { $this->showList('activity','Activity Log'); }
    public function admin(): void { $this->showList('admin','Admin Log'); }
    public function login(): void { $this->showList('login','Login History'); }
    public function error(): void { $this->showList('error','Error Log'); }

    public function detail(int $id): void
    {
        $type=(string)($_GET['type']??'activity');
        $this->authorize($type);
        $row=$this->service->detail($type,$id);
        if(!$row){http_response_code(404);$this->view('errors/404',['title'=>'Không tìm thấy nhật ký']);return;}
        $this->view('admin/system-log/detail',['title'=>'Chi Tiết Nhật Ký','type'=>$type,'log'=>$row]);
    }

    public function resolve(int $id): void
    {
        if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST'){$this->redirect('admin/system-log/error');}
        $this->authorize('error','manage');
        Csrf::verify();
        $resolved=(int)($_POST['resolved']??1)===1;
        $this->service->resolve($id,(int)Session::get('user_id'),$resolved);
        Session::flash('system_log_msg',$resolved?'Đã đánh dấu lỗi là đã xử lý.':'Đã mở lại lỗi.');
        $this->redirect('admin/system-log/detail/'.$id.'?type=error');
    }

    public function purge(): void
    {
        if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST'){$this->redirect('admin/system-log');}
        if(!$this->policy->canManageAll())$this->deny();
        Csrf::verify();
        $months=(int)($_POST['months']??12);
        $result=$this->service->purge($months);
        SystemLogger::admin('purge','system_log','logs',null,"Xóa log cũ hơn {$months} tháng",[],['deleted'=>$result]);
        Session::flash('system_log_msg','Đã dọn '.number_format(array_sum($result)).' bản ghi cũ.');
        $this->redirect('admin/system-log');
    }

    public function export(string $type='activity'): void
    {
        if(!SystemLog::validType($type)){http_response_code(404);return;}
        $this->authorize($type,'export');
        $rows=$this->service->export($type,$_GET);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="system-log-'.$type.'-'.date('Ymd-His').'.csv"');
        echo "\xEF\xBB\xBF";
        $out=fopen('php://output','wb');
        $headers=$rows?array_keys((array)$rows[0]):['id','created_at'];
        fputcsv($out,$headers);
        foreach($rows as $row){$values=[];foreach((array)$row as $value){$value=is_scalar($value)||$value===null?(string)$value:json_encode($value);if(preg_match('/^[=+\-@]/',$value))$value="'".$value;$values[]=$value;}fputcsv($out,$values);}
        fclose($out);
    }

    private function showList(string $type,string $title): void
    {
        $this->authorize($type);
        $this->view('admin/system-log/list',['title'=>$title,'logs'=>$this->service->listing($type,$_GET)]);
    }

    private function authorize(string $type,string $ability='view'): void
    {
        if(!$this->policy->can($type,$ability))$this->deny();
    }

    private function deny(): never
    {
        Auth::logSecurityEvent('system_log_access_denied','Role '.(int)Session::get('user_role_id').' attempted to access system logs');
        Session::flash('error_msg','Bạn không có quyền xem loại nhật ký này.','alert alert-danger');
        $this->redirect('admin/dashboard');
    }
}
