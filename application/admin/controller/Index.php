<?php
namespace app\admin\controller;

use think\Controller;
use think\Model;
use think\Session;
use think\Cookie;

class Index extends Controller {
	/*
		*	如果没有admin_id 就跳转到后台登录页面
	*/
	public function _initialize(){
		// 检测如果没有admin_id 跳转到登录界面
		if(!Session::has('admin_id')){
			$this->redirect(url('admin/login/index'));
		}
	}
	public function index(){
		return view();
	}
		/*	退出登录	*/
	public function go_out(){
		$this->redirect('admin/login/index');
	}
}

?>