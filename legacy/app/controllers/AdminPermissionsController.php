<?php
class AdminPermissionsController extends Controller{public function __construct(){Auth::requireRole(1);}public function index():void{$this->redirect('admin/roles');}}
