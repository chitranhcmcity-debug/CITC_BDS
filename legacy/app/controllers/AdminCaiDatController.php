<?php
class AdminCaiDatController extends Controller
{
    public function __construct(){ Auth::requireRole(1); }
    public function index(): void
    {
        $this->view('admin/cai-dat/index',['title'=>'Cài đặt hệ thống - '.SITE_NAME,'settings'=>(new SettingService())->all(),'backups'=>(new SystemBackupService())->history()]);
    }
    public function save(): void
    {
        $this->post(); Csrf::verify(); $result=(new SettingService())->update($_POST,(int)Session::get('user_id'));
        Session::flash('setting_msg',$result['success']?'Lưu cài đặt thành công!':'Dữ liệu chưa hợp lệ: '.implode(' ',$result['errors'])); $this->redirect('admin/cai-dat');
    }
    public function testEmail(): void
    {
        $this->jsonHeader(); if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST'||!Csrf::verify(false)){$this->json(false,'Yêu cầu bảo mật không hợp lệ.');return;}
        $email=trim((string)($_POST['test_email']??''));if(!filter_var($email,FILTER_VALIDATE_EMAIL)){$this->json(false,'Địa chỉ email không hợp lệ.');return;}
        $ok=Email::send($email,'Kiểm tra SMTP - '.SITE_NAME,'<p>Kết nối SMTP hoạt động bình thường.</p><p>Thời gian: '.date('d/m/Y H:i:s').'</p>',['isHtml'=>true]);
        $this->json($ok,$ok?'Gửi email kiểm tra thành công.':'Gửi email thất bại, vui lòng kiểm tra cấu hình SMTP.');
    }
    public function testPayos(): void
    {
        $this->jsonHeader();if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST'||!Csrf::verify(false)){$this->json(false,'Yêu cầu bảo mật không hợp lệ.');return;}
        $s=(new SettingService())->all();$client=(string)($s['payos_client_id']??'');$key=(string)($s['payos_api_key']??'');
        if($client===''||$key===''){$this->json(false,'Chưa cấu hình Client ID hoặc API Key PayOS.');return;}
        $ch=curl_init('https://api-merchant.payos.vn/v2/payment-requests/0');curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10,CURLOPT_HTTPHEADER=>['x-client-id: '.$client,'x-api-key: '.$key]]);curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$error=curl_error($ch);curl_close($ch);
        $ok=$error===''&&$status!==0&&!in_array($status,[401,403],true);$this->json($ok,$ok?'Kết nối PayOS thành công.':'Không thể xác thực PayOS'.($error!==''?': '.$error:'. Kiểm tra lại thông tin đăng nhập.'));
    }
    public function cacheClear(): void
    {
        $this->post();Csrf::verify();$type=(string)($_POST['type']??'all');$count=(new SystemCacheService())->clear($type);SystemLogger::admin('clear_cache','cai_dat','cache',null,'Xóa cache hệ thống',[],['type'=>$type,'files'=>$count],(int)Session::get('user_id'));Session::flash('setting_msg',"Đã xóa {$count} tệp cache.");$this->redirect('admin/cai-dat');
    }
    public function backup(): void
    {
        $this->post();Csrf::verify();$type=(string)($_POST['type']??'database');$ok=(new SystemBackupService())->create($type,(int)Session::get('user_id'));SystemLogger::admin('backup','cai_dat','backup',null,'Sao lưu hệ thống',[],['type'=>$type,'success'=>$ok],(int)Session::get('user_id'));Session::flash('setting_msg',$ok?'Đã tạo bản sao lưu.':'Không thể tạo bản sao lưu.');$this->redirect('admin/cai-dat');
    }
    public function backupHistory(): void { $this->index(); }
    public function downloadBackup(int$id): void
    {
        $file=(new SystemBackupService())->file($id);if(!$file){http_response_code(404);die('Không tìm thấy bản sao lưu.');}header('Content-Type: application/octet-stream');header('Content-Disposition: attachment; filename="'.basename($file['path']).'"');header('Content-Length: '.filesize($file['path']));readfile($file['path']);exit;
    }
    public function deleteBackup(int$id): void
    {
        $this->post();Csrf::verify();$ok=(new SystemBackupService())->delete($id);SystemLogger::admin('delete','cai_dat','backup',$id,'Xóa bản sao lưu',[],['success'=>$ok],(int)Session::get('user_id'));Session::flash('setting_msg',$ok?'Đã xóa bản sao lưu.':'Không thể xóa bản sao lưu.');$this->redirect('admin/cai-dat');
    }
    public function uploadLogo(): void { $this->uploadSettingImage('logo','logo'); }
    public function uploadFavicon(): void { $this->uploadSettingImage('favicon','favicon'); }
    private function uploadSettingImage(string$field,string$key): void{$this->post();Csrf::verify();$file=(new SettingUploadService())->image($field,$key);if($file)(new SettingService())->update([$key=>$file],(int)Session::get('user_id'));SystemLogger::admin('upload','cai_dat','settings',null,'Tải lên '.$key,[],[$key=>$file],(int)Session::get('user_id'));Session::flash('setting_msg',$file?'Tải ảnh thành công.':'Ảnh không hợp lệ (PNG/JPG/WebP/ICO, tối đa 2MB).');$this->redirect('admin/cai-dat');}
    private function post():void{if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST')$this->redirect('admin/cai-dat');}
    private function jsonHeader():void{header('Content-Type: application/json; charset=utf-8');}
    private function json(bool$success,string$message):void{echo json_encode(['success'=>$success,'message'=>$message],JSON_UNESCAPED_UNICODE);}
}
