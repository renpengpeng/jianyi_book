<?php
namespace app\index\controller;

use think\Controller;
use think\Model;
use think\Session;
use think\Cookie;

class Shop extends Controller {
	public function _initialize(){
		// 如果有会员信息赋值会员信息
		if(Session::has('user_info')){
			$userArr 	=	Session::get('user_info');
		}else{
			$userArr 	=	['user_id'=>0];
		}

		$this->assign('userArr',$userArr);
	}
	public function index($id){
		// 判断id是否为数字不是数字则重定向到提示页面
		if(!is_numeric($id)){
			$this->redirect(url('index/index/cavaet',['msg'=>'商品参数错误']));
		}else{
			// 判断数据库内是否有此id
			$hasData 		=	Model('BookGoods')->get($id);

			if(!$hasData){
				$this->redirect(url('index/index/cavaet',['msg'=>'商品参数错误']));
			}else{
				$hasData 	=	$hasData->toArray();
			}
		}

		// 反转换html
		$hasData['details'] 	=	htmlspecialchars_decode($hasData['details']);

		// 转换时间戳
		if(!empty($hasData['time'])){
			$hasData['time'] 	=	date("Y-m-d H:i",$hasData['time']);
		}

		// 分割副图x4并向数组添加：pic1,pic2,pic3,pic4,pic5
		$fuPicArr 	=	explode(',', $hasData['vice_img']);

		// 替换null为''
		foreach ($fuPicArr as $key => $value) {
			if($fuPicArr[$key] == 'null'){
				$fuPicArr[$key] 	=	'';
			}
		}
		
		$hasData['pic1'] 			=	$hasData['main_img'];
		$hasData['pic2']			=	$fuPicArr[0];
		$hasData['pic3']			=	$fuPicArr[1];
		$hasData['pic4']			=	$fuPicArr[2];
		$hasData['pic5']			=	$fuPicArr[3];

		// 获取meta
		$meta 			=	getMeta('shop',$id,'','');
		// 获取所有分类(header)
		$allCate 		=	getCateForIndexA();
		// 获取侧边栏数据
		$sidebarData 	=	getListSidebar();
		// 获取setting
		$setting 		=	getSetting();

		// 查询评论数据
		$comment 		=	Model('BookComment')
								->where('good_id',$id)
								->order('comment_id desc')
								->limit($setting['shop_comment_list_show_num'])
								->select();

		// 如果查询到评论数据转换为数组并转换时间戳 * 查询会员昵称
		if($comment){
			$comment 	=	$comment->toArray();
			foreach ($comment as $key => $value) {
				// 转换时间戳
				$comment[$key]['comment_time'] 	=	date('Y-m-d H:i:s',$comment[$key]['comment_time']);
				$comment[$key]['reply_time'] 	=	date('Y-m-d H:i:s',$comment[$key]['reply_time']);
				// 转换评论html
				$comment[$key]['content'] 		=	htmlspecialchars_decode($comment[$key]['content']);
				// 查询会员昵称 与头像
				$nickname 	=	Model('BookUsers')->get($comment[$key]['user_id']);
				if($nickname){
					$nickname 	=	$nickname->toArray();
					$comment[$key]['nickname'] 	=	$nickname['bbsname'];
					$comment[$key]['head_pic'] 	=	$nickname['head_pic'];
				}
			}
		}


		$this->assign('meta',$meta);
		$this->assign('allCate',$allCate);
		$this->assign('sidebarData',$sidebarData);
		$this->assign('shopData',$hasData);
		$this->assign('commentData',$comment);
		return view();
	}
	/*
		*	按照分数筛选评论
	*/
	public function fractiontoclick($fraction,$shopid){
		if(!is_numeric($fraction) || $fraction>5 || $fraction < 0 || !$shopid){
			return json(['code'=>0,'msg'=>'不在范围']);
		}

		// 获取setting
		$setting 		=	getSetting();

		// 按照分数筛选评论
		$commentData 	=	Model('BookComment')
								->where([
									'good_id' 	=>	$shopid,
									'fraction'	=>	$fraction
								])
								->order('comment_id desc')
								->limit($setting['shop_comment_list_show_num'])
								->select();
		if($commentData){
			$commentData	=	$commentData->toArray();

			foreach ($commentData as $key => $value) {
				// 转换评论html
				$commentData[$key]['content'] 	=	htmlspecialchars_decode($commentData[$key]['content']);
				// 转换时间戳
				$commentData[$key]['comment_time'] 		=	date('Y-m-d H:i:s',$commentData[$key]['comment_time']);
				$commentData[$key]['reply_time'] 		=	date('Y-m-d H:i:s',$commentData[$key]['reply_time']);
				// 查询会员昵称 与头像
				$nickname 	=	Model('BookUsers')->get($commentData[$key]['user_id']);
				if($nickname){
					$nickname 	=	$nickname->toArray();
					$commentData[$key]['nickname'] 	=	$nickname['bbsname'];
					$commentData[$key]['head_pic'] 	=	$nickname['head_pic'];
				}
			}
		}

		$this->assign('commentData',$commentData);

		return view();
	}
	/*
		*	添加购物车
		*	参数：userid(用户id)   shopid(商品id) 	num(数量)
	*/
	public function add_cart(){

		if(!input('?post.userid') || !input('?post.shopid') || !input('?post.num')){
			return json(['code'=>0,'msg'=>'非法的参数']);
		}else{
			$userid 	=	input('userid');
			$shopid 	=	input('shopid');
			$num 		=	input('num');
		}

		// 查询用户是否存在或者是否被锁定 
		$hasUser 		=	Model('BookUsers')->get($userid);
		if($hasUser){
			$hasUser 	=	$hasUser->toArray();
			// 判断是否被锁定
			if($hasUser['status'] == 0){
				return json(['code'=>0,'msg'=>'用户被锁定 暂时不能加入购物车']);
			}
		}else{
			return json(['code'=>0,'msg'=>'用户不存在']);
		}

		// 查询商品数量
		$hasShop 		=	Model('BookGoods')->get($shopid);
		if(!$hasShop){
			return json(['code'=>0,'msg'=>'商品不存在']);
		}

		// 判断是否有此商品如果有此商品 购物车内数量+1
		$hasCart 		=	Model('BookCarts')
								->where([
									'user_id' 	=> 	$userid,
									'good_id' 	=> 	$shopid
								])
								->find();
		if($hasCart){
			$addCart 	=	Model('BookCarts')->where(['user_id' 	=> 	$userid,'good_id' 	=> 	$shopid])->setInc('good_num');
			if($addCart){
				return json(['code'=>1,'msg'=>'添加购物车成功']);
			}else{
				return json(['code'=>0,'msg'=>'添加购物车失败']);
			}


		}

		// 组合插入数据库的数据
		$addData 		=	[
			'user_id' 		=>	$userid,
			'good_id' 		=>	$shopid,
			'good_num' 		=>	$num,
			'create_time' 	=>	@time()
		];	

		// 开始加入购物车
		$beginAdd 		=	Model('BookCarts')->insert($addData);

		if($beginAdd){
			return json(['code'=>1,'msg'=>'添加购物车成功']);
		}else{
			return json(['code'=>0,'msg'=>'添加购物车失败']);
		}

	}
}

?>