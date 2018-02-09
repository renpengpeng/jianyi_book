<?php
namespace app\admin\controller;

use think\Session;
use think\Controller;
use think\Cookie;
use think\Model;

class Commodity extends Controller{
	// 自动加载 如果没有session : admin_id 跳转到登录页面
	public function _initialize(){
		if(!Session::has('admin_id')){
			$this->redirect(url('admin/login/index'));
		}
	}
	public function index(){
		// 跳转到全部商品
		$this->redirect(url('admin/commodity/all_commodity'));
	}
	/*
		*	全部商品
	*/
	public function all_commodity(){
		return view();
	}
	/*
		*	发布商品
	*/
	public function create_commodity(){
		return view();
	}
}

?>