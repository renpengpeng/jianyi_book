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
		// 获取公共参数
		$commonData 	=	getCommonData();

		$this->assign('commonData',$commonData);
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
			// 查询收货地址数量
			$userAddress 	=	Model('BookAddress')->where('user_id',USERID)->count();


		// 赋值信息
			// 赋值用户信息
			$this->assign('userMessage',$userMessage);
			// 赋值订单 数量
			$this->assign('countOrder',$userOrders);
			// 赋值收货地址数量
			$this->assign('userAddress',$userAddress);


		return view();
	}
	/*
		*	订单管理页面
	*/
	public function order(){
		// 获取setting
		$setting 	=	getSetting();
		// 获取page
		if(!input('page')){
			$page 	=	1;
		}else{
			$page 	=	input('page');
			if(!is_numeric($page)){
				$this->redirect(url('index/index/cavaet',['msg'=>'page参数不符合要求']));
			}
		}

		// 获取用户所有订单
		$userOrders = Model('BookOrders')
						->where('user_id',USERID)
						->order('add_time desc')
						->page($page)
						->limit($setting['index_list_show_num'])
						->select();

		// 分页
		$pageation 	=	Model('BookOrders')
						->where('user_id',USERID)
						->paginate($setting['index_list_show_num']);

		$this->assign('orderData',$userOrders);
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
			return $this->error('参数错误');
		}else{
			$id 	=	input('post.id');
			if(!is_numeric($id)){
				return $this->error('参错错误');
			}
		}

		// 开始删除
		$delCart 	=	Model('BookCarts')->where('cart_id',$id)->delete();

		if($delCart){
			return $this->success('删除成功');
		}else{
			return $this->error('删除失败');
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
		// 获取用户信息
		$userData 	=	Model('BookUsers')->get(USERID)->toArray();

		// 转换时间
		$userData['reg_time'] 	=	date('Y-m-d H:i:s',$userData['reg_time']);

		$this->assign('userData',$userData);
		return view();
	}
	/*
		*	个人资料修改
	*/
	public function modify(){
		// 获取用户信息
		$userData 	=	Model('BookUsers')->get(USERID)->toArray();

		$this->assign('userData',$userData);
		return view();
	}
	/*
		*	密码修改
	*/
	public function pass_modify(){
		return view();
	}
	/*
		*	密码修改数据处理
	*/
	public function pass_modify_with(){
		$data 	=	input('post.');
		if(!$data['password'] || !$data['newPass'] || !$data['newPassOne']){
			return $this->error('请填写完整');
		}

		// 查找用户数据
		$userMessage 	=	Model('BookUsers')->get(USERID);

		// 如果用户状态
		if($userMessage){
			$userMessage 	=	$userMessage->toArray();
		}else{
			Session::delete('user_info');
			return $this->success('不存在的用户');
		}

		// 开始判断长度
		if(strlen($data['newPass']) > 16){
			return $this->error('密码长度过长');
		}

		if(strlen($data['newPass']) < 5){
			return $this->error('密码长度过短');
		}

		if(!preg_match("/^[a-zA-Z0-9]+$/", $data['newPass'])){
			return $this->error('密码只能为字母或者数字');
		}

		// 对比密码是否正确
		if(md5($data['password']) != $userMessage['password']){
			return $this->error('原密码错误');
		}

		if($data['newPassOne'] != $data['newPass']){
			return $this->error('两次密码不相同');
		}

		// 开始更新数据
		$update 	=	Model('BookUsers')->where('user_id',USERID)->update(['password'=>md5($data['newPass'])]);

		if($update){
			Session::delete('user_info');
			return $this->success('修改密码成功');
		}else{
			return $this->error('修改密码失败');
		}
	}
	/*
		*	收货地址管理
	*/
	public function address(){

		// 根据用户Id来查询收货地址
		$addressData 		=	Model('BookAddress')
									->where('user_id',USERID)
									->order('address_id desc')
									->select();

		$this->assign('addressData',$addressData);
		return view();
	}
	/*
		*	设置收货地址为默认
	*/
	public function address_default(){
		if(input('?post.id')){
			$id 	=	input('post.id');
			// 检测是否为数字
			if(!is_numeric($id)){
				return json(['code'=>0,'msg'=>'请填写完整']);
			}
		}

		// 查询收货地址是否存在
		$hasAddress 	=	Model('BookAddress')->get($id);

		if(!$hasAddress){
			return json(['code'=>0,'msg'=>'收货地址不存在']);
		}else{
			$hasAddress 	=	$hasAddress->toArray();
			// 如果收货地址已经是默认 终止
			if($hasAddress['default'] == 1){
				return json(['code'=>0,'msg'=>'已经是默认']);
			}
		}

		// 检测是否属于此用户
		if($hasAddress['user_id'] != USERID){
			return json(['code'=>0,'msg'=>'参数错误']);
		}

		// 检测此用户是否有默认收货地址 
		// 如果有 则去除之前的默认地址 设此为默认
		$elseAddress 	=	Model('BookAddress')
								->where([
									'user_id' 	=> 	USERID,
									'default' 	=>	1
								])
								->find();
		if($elseAddress){
			$elseAddress 	=	$elseAddress->toArray();
			$updateElse 	=	Model('BookAddress')->where('address_id',$elseAddress['address_id'])->update(['default'=>0]);
			if(!$updateElse){
				return json(['code'=>0,'msg'=>'设置默认失败']);
			}
		}

		// 开始设收货地址
		$updateAddress 	=	Model('BookAddress')->where('address_id',$id)->update(['default'=>1]);
		if(!$updateAddress){
			return json(['code'=>0,'msg'=>'设置默认失败']);
		}else{
			return json(['code'=>1,'msg'=>'设置默认成功']);
		}

	}
	/*
		*	删除收货地址
	*/
	public function address_del(){
		if(input('?post.id')){
			$id 	=	input('post.id');
			// 检测是否为数字
			if(!is_numeric($id)){
				return json(['code'=>0,'msg'=>'参数错误']);
			}
		}

		// 查询收货地址是否存在
		$hasAddress 	=	Model('BookAddress')->get($id);
		if($hasAddress){
			$hasAddress 	=	$hasAddress->toArray();
		}else{
			return json(['code'=>0,'msg'=>'收货地址不存在']);
		}

		// 检测是否属于此用户
		if($hasAddress['user_id'] != USERID){
			return json(['code'=>0,'msg'=>'参数错误']);
		}

		// 开始删除
		$del 	= 	Model('BookAddress')->where('address_id',$id)->delete();

		if($del){
			return json(['code'=>1,'msg'=>'删除成功']);
		}else{
			return json(['code'=>0,'msg'=>'删除失败']);
		}
	}
	/*
		*	添加收货地址
	*/
	public function address_new(){
		// 判断收货地址数量 不能超过5个
		$addressCount 	=	Model('BookAddress')->where('user_id',USERID)->count();
		if($addressCount >= 5){
			return $this->error('收货地址达到最大限制');
		}

		$data 	=	input('post.');
		
		if(!$data['province'] || !$data['city'] || !$data['address']){
			return $this->error('请填写完整');
		}

		// 如果市区等于0证明市区参数没有选择
		if(!$data['district']){
			return $this->error('县(区) 参数没有选择');
		}

		$data['address'] 	=	htmlspecialchars($data['address']);

		// 添加会员信息
		$data['user_id'] 	=	USERID;

		// 判断邮政编码
		if(isset($data['code'])){
			if(empty($data['code'])){
				$data['code'] 	=	'000000';
			}else{
				if(strlen($data['code']) != 6){
					return $this->error('邮政编码长度不符(可不填)');
				}
			}
		}

		// 添加时间戳
		$data['create_time'] 	=	@time();

		// 开始添加
		$beginInsert 	=	Model('BookAddress')->insert($data);

		if($beginInsert){
			return $this->success('收货地址添加成功');
		}else{
			return $this->error('收货地址添加失败');
		}

	}
	/*
		*	当用户被锁定， 出现此页面
	*/
	public function lock(){
		return view();
	}
}

?>