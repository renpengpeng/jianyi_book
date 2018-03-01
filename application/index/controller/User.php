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
		if(!Session::has('user_info')){
			$this->redirect(url('index/login/index'));

		}else{
			// 定义用户ID
			$userArr 	=	Session::get('user_info');
			define('USERID',$userArr['user_id']);
		}

		// 获取meta
		$meta 		=	getMeta('user','','','');
		// 获取所有分类
		$allCate 	=	getCateForIndexA();

		$this->assign('meta',$meta);
		$this->assign('allCate',$allCate);
	}
	/*
		*	用户中心首页
	*/
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
			$this->assign('countOrder',$userOrders);


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
		// 获取setting
		$setting 		=	getSetting();
		$listShowNum 	=	$setting['index_list_show_num'];

		// 统计所属用户的购物车数量
		$shopCount 		=	Model('BookCarts')->where('user_id',USERID)->count();

		// 获取page
		if(input('page')){
			$page 	=	input('page');
			if(!is_numeric($page)){
				$this->redirect(url('index/index/cavaet',['msg'=>'错误的{page}']));
			}else{
				// 判断page是否超出
				if(ceil($shopCount / $listShowNum) < $page){
					$this->redirect(url('index/index/cavaet',['msg'=>'页码超出(无效)']));
				}
			}
		}else{
			$page 	=	1;
		}

		// 获取购物车所有商品按照id倒序
		$shopData 	 	=	Model('BookCarts')
								->where('user_id',USERID)
								->order('cart_id desc')
								->page($page)
								->limit($listShowNum)
								->select();

		// 分页
		$pageination 	=	Model('BookCarts')
								->where('user_id',USERID)
								->order('cart_id desc')
								->paginate($listShowNum);
		if($shopData){
			$shopData 	=	$shopData->toArray();
			// 添加商品信息:商品编码 运费 主图 
			foreach ($shopData as $key => $value) {
				// 查询商品信息
				$findShop 	=	Model('BookGoods')->get($shopData[$key]['good_id']);
				if($findShop){
					// 添加商品有效信息
					$shopData[$key]['has'] 	=	1;

					// 商品转换数组
					$findShop 		=	$findShop->toArray();

					// 添加商品编码
					$shopData[$key]['numbering'] 	=	$findShop['numbering'];
					// 添加商品名称
					$shopData[$key]['shop_title'] 	=	$findShop['title'];
					// 添加商品图片
					$shopData[$key]['pic'] 			=	$findShop['main_img'];
				}else{
					// 添加商品失效信息
					$shopData[$key]['has'] 	=	0;

				}
			}
		}

		$this->assign('cartData',$shopData);
		$this->assign('pageination',$pageination);
		$this->assign('cartCount',$shopCount);
		return view();
	}
	/*
		*	删除购物车
	*/
	public function cart_del(){
		if(!input('?post.id')){
			return json(['code'=>0,'msg'=>'参数错误']);
		}else{
			$id 	=	input('post.id');
			if(!is_numeric($id)){
				return json(['code'=>0,'msg'=>'参数错误']);
			}
		}

		// 开始删除
		$delCart 	=	Model('BookCarts')->where('cart_id',$id)->delete();

		if($delCart){
			return json(['code'=>1,'msg'=>'删除成功']);
		}else{
			return json(['code'=>0,'msg'=>'删除失败']);
		}
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