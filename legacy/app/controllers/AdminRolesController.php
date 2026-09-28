<?php
class AdminRolesController extends Controller
{
 private RoleService$service;public function __construct(){Auth::requireRole(1);$this->service=new RoleService();}
 public function index():void{$this->view('admin/role/index',array_merge(['title'=>'Phân quyền & Vai trò - '.SITE_NAME],$this->service->page(isset($_GET['role'])?(int)$_GET['role']:null)));}
 public function create():void{if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST'){$this->index();return;}Csrf::verify();$this->flash($this->service->save(null,$_POST,(int)Session::get('user_id')));}
 public function edit(int$id):void{if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){Csrf::verify();$this->flash($this->service->save($id,$_POST,(int)Session::get('user_id')));}$this->view('admin/role/index',array_merge(['title'=>'Sửa vai trò'], $this->service->page($id)));}
 public function delete(int$id):void{if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST')$this->redirect('admin/roles');Csrf::verify();Session::flash('rbac_msg',$this->service->delete($id,(int)Session::get('user_id'))?'Đã xóa vai trò.':'Không thể xóa vai trò hệ thống hoặc đang được sử dụng.');$this->redirect('admin/roles');}
 public function assignPermission():void{if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST')$this->redirect('admin/roles');Csrf::verify();$ok=$this->service->permissions((int)($_POST['role_id']??0),$_POST['permissions']??[],(int)Session::get('user_id'));Session::flash('rbac_msg',$ok?'Đã cập nhật quyền.':'Không thể thay đổi quyền Super Admin.');$this->redirect('admin/roles?role='.(int)($_POST['role_id']??0));}
 public function assignRole():void{if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST')$this->redirect('admin/roles');Csrf::verify();$ok=$this->service->assignUser((int)($_POST['user_id']??0),(int)($_POST['role_id']??0),(int)Session::get('user_id'));Session::flash('rbac_msg',$ok?'Đã gán vai trò cho người dùng.':'Không thể gán vai trò.');$this->redirect('admin/roles');}
 private function flash(array$r):never{Session::flash('rbac_msg',$r['message']);$this->redirect('admin/roles');}
}
