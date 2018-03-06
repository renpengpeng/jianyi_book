<?php
namespace app\index\controller;

use think\Cookie;
use think\Session;
use think\Model;
use think\Controller;

class Order extends Controller {
	/*
		*	初始化 判断用户是否登录
	*/
	public function _initialize(){
		if(!Session::has('user_info')){
			$this->redirect(url('index/login/index'));
		}else{
			$userMessage 	=	Session::get('user_info');
			define('USERID',$userMessage['user_id']);
		}

		// 获取meta
		$meta 	=	getMeta();
		
		// 获取公共参数
		$commonData  	=	getCommonData();


		$this->assign('meta',$meta);
		$this->assign('commonData',$commonData);
	}
	public function index(){
		// 获取good_id与购买数量
		if(!input('?good_id') || !input('?buyNum')){
			$this->redirect(url('index/index/cavaet',['msg'=>'参数缺少不可购买']));
		}else{
			$goodId 	=	input('good_id');
			$buyNum 	= 	input('buyNum');
		}

		// 获取用户收货地址
		$address 		=	Model('BookAddress')->where('user_id',USERID)->order('default','asc')->select();

		// 获取商品信息
		$goodMessage 	=	Model('BookGoods')->get($goodId)->toArray();

		// 计算金额
		$buyPrice 		=	$buyNum*$goodMessage['price'];

		$this->assign('goodMessage',$goodMessage);
		$this->assign('buyNum',$buyNum);
		$this->assign('address',$address);
		$this->assign('buyPrice',$buyPrice);

		return view();		
	}
}