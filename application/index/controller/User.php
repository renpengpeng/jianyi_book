<?php
namespace app\index\controller;

use think\Controller;
use think\Model;
use think\Session;
use think\Cookie;

class User extends Controller {
	/*
		*	自动加载获取用户ID
	*/
	public function _initialize(){
		// 判断如果没有session则跳转登录页面
		// dump(Session::has('user_id'));exit;
		if(Session::has('user_id')){
			// 赋值用户Id
			define('USERID',Session::get('user_id'));

			// 验证用户是否被锁定
			$userStatus	=	Model('BookUsers')->where('user_id',USERID)->find()->toArray();

			if(empty($userStatus['status']) || $userStatus['status'] == 0){
				$this->redirect(url('index/login/lock'));
			}

		}else{
			$this->redirect(url('index/login/index'));
		}	
	}
	public function index(){
		// 获取一些信息
			// 获取用户信息
			$userMessage 	= 	Model('BookUsers')->where('user_id',USERID)->find();
			// 查询订单数量
			$userOrders		=	Model('BookOrders')->where('user_id',USERID)->count();

		// 赋值信息
			// 赋值用户信息
			$this->assign('userMessage',$userMessage);
			// 赋值订单 数量
			$this->assign('userOrders',$userOrders);


		return view();
	}
	/*
		*	订单管理页面
	*/
	public function order(){
		// 获取用户所有订单
		$userOrders = Model('BookOrders')
						->where('user_id',USERID)
						->order('add_time desc')
						->select();

		// 分页
		$pageation 	=	Model('BookOrders')
						->where('user_id',USERID)
						->paginate(10);

		$this->assign('userOrders',$userOrders);
		$this->assign('pageation',$pageation);

		return view();
	}
	/*
		*	订单详情
	*/
	public function order_details(){
		return view();
	}
	/*
		*	退货管理页面
	*/
	public function retreat(){
		return view();
	}
	/*
		*	交易记录
	*/
	public function transaction(){
		return view();
	}
	/*
		*	购物车
	*/
	public function cart(){
		return view();
	}
	/*
		*	收藏夹
	*/
	public function favorites(){
		return view();
	}
	/*
		*	余额
	*/
	public function balance(){
		return view();
	}
	/*
		*	充值
	*/
	public function recharge(){
		return view();
	}
	/*
		*	个人信息
	*/
	public function info(){
		return view();
	}
	/*
		*	个人资料修改
	*/
	public function modify(){
		return view();
	}
	/*
		*	密码修改
	*/
	public function pass_modify(){
		return view();
	}
	/*
		*	当用户被锁定， 出现此页面
	*/
	public function lock(){
		return view();
	}
}

?>