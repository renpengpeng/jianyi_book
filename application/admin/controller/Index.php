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
		// 获取商品总数
		$goodCount 			=	Model('BookGoods')->count();
		// 获取出售中的商品
		$goodYes 			=	Model('BookGoods')->where('status',1)->count();
		// 获取仓库中的商品
		$goodNo 			=	Model('BookGoods')->where('status',0)->count();
		// 获取没有卖出去的商品
		$goodNoBuy 			=	Model('BookGoods')->where(['status'=>1,'scale'=>0])->count();
		// 获取订单数量
		$orderCount 		=	Model('BookOrders')->count();
		// 获取代发货订单
		$orderDelivered 	=	Model('BookOrders')->where('order_status',1)->count();
		// 获取正在路上的订单
		$orderOnWay 		=	Model('BookOrders')->where('order_status',2)->count();
		// 查询交易完成的订单
		$orderYes 			=	Model('BookOrders')->where('order_status',5)->count();
		// 统计商品分类数量
		$cateCount 			=	Model('BookCates')->count();

		$this->assign('goodCount',$goodCount);
		$this->assign('goodYes',$goodYes);
		$this->assign('goodNo',$goodNo);
		$this->assign('goodNoBuy',$goodNoBuy);
		$this->assign('orderCount',$orderCount);
		$this->assign('orderDelivered',$orderDelivered);
		$this->assign('orderOnWay',$orderOnWay);
		$this->assign('orderYes',$orderYes);
		$this->assign('cateCount',$cateCount);
		return view();
	}
	/*
		*	提示信息
		*	接收参数：$msg ->提示文字
	*/
	public function cavaet($msg='默认提示信息'){
		$this->assign('msg',$msg);
		return view();
	}
	/*	退出登录	*/
	public function go_out(){
		Session::delete('admin_id');
		$this->redirect('admin/login/index');
	}
}

?>