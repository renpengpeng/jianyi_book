<?php
namespace app\admin\controller;

use think\Model;
use think\Session;
use think\Cookie;
use think\Controller;

/*
	*	后台订单管理
*/

class Order extends Controller{
	public function _initialize(){
		if(!Session::has('admin_id')){
			$this->redirect(url('admin/login/index'));
		}else{
			$adminSession 	=	Session('admin_id');
		}
	}
	/*
		*	根据status来选择订单
		*	status 	=> 	0 全部订单
		*	status 	=>	1 未处理的订单
		*	status 	=>	2 进行中的订单
		*	status  =>	4 已完成的订单
	*/
	public function index($status=0){
		return view();
	}
}