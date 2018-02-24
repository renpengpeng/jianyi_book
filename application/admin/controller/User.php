<?php
namespace app\admin\controller;

use think\Model;
use think\Controller;
use think\Session;
use think\Cookie;

class User extends Controller {
	public function _initialize(){
		// 判断Session id
		if(!Session::has('admin_id')){
			$this->redirect(url('admin/login/index'));
		}else{
			$adminSession 	=	Session('admin_id');
			// 如果用户被锁定 无权任何操作
			if($adminSession['status'] == 0){
				$this->redirect(url('admin/index/cavaet',['msg'=>'已经被锁定 无任何权限']));
			}
			// 如果权限为运营团队 此页面禁止进入
			if($adminSession['permissions'] == 2 || $adminSession['permissions'] == 1){
				$this->redirect(url('admin/index/cavaet',['msg'=>'权限不足，禁止进入']));
			}
		}
	}
	/*
	``` 接收变量 $type  
			值： administer 为管理员  
			值： 为空或者为user 为普通会员
		接收变量 $status
			值：	 0 锁定状态
			值：	 1 正常状态 

		默认$type = user  $status = 1
	*/
	public function index($type = 'user' ,$status = 1,$page = 1){
		// 判断状态
		if(!is_numeric($status)){
			return json(['code'=>0,'msg'=>'status必须为数字']);
		}
		if($status != 1){
			if($status != 0){
				return json(['code'=>0,'msg'=>'status必须为0或1']);
			}
		}


		// 获取setting信息
		$setting =	getSetting();

		// 赋值后台列表显示数量
		$adminListShowNum 	=	$setting['admin_list_show_num'];

		// 根据type 来查找数据
		switch ($type) {
			case 'user':
				$result  		=  Model('BookUsers')
										->where('status',$status)
										->order('user_id desc')
										->limit($adminListShowNum)
										->page($page)
										->select();

				$pageination 	=	Model('BookUsers')
										->where('status',$status)
										->paginate($adminListShowNum);
			break;

			case 'administer':
				$result  		=	Model('BookAdmins')
										->where('status',$status)
										->order('admin_id desc')
										->limit($adminListShowNum)
										->page($page)
										->select();

				$pageination 	=	Model('BookAdmins')
										->where('status',$status)
										->paginate($adminListShowNum);
			break;

			default:
				return json(['code'=>0,'msg'=>'未定义的type']);
			break;
		}

		// 赋值 result 
		$this->assign('userArr',$result);
		// 赋值type
		$this->assign('type',$type);
		// 赋值分页
		$this->assign('pageination',$pageination);
		// 赋值status 
		$this->assign('status',$status);

		return view();
	}
	/*
		*	用户个人信息展示
		*	必须传入变量 ： $type => 类型(user->普通用户 , administer->管理员)
		*				   $id 	 => 用户id(user_id) 或者 管理员id(admin_id)
	*/
	public function show($type,$id){
		if($type == 'user'){
			$ctype = 'BookUsers';
		}elseif($type == 'administer'){
			$ctype = 'BookAdmins';
		}else{
			return json(['code'=>0,'msg'=>'type参数错误']);
		}

		// 开始获取信息
		$message = Model($ctype)->get($id)->toArray();

		// 转换时间戳
		if(isset($message['reg_time'])){
			$message['reg_time']	=	date('Y-m-d H:i:s',$message['reg_time']);
		}

		if(isset($message['last_login'])){
			$message['last_login']	=	date('Y-m-d H:i:s',$message['last_login']);
		}

		if(isset($message['create_time']) && is_numeric($message['create_time'])){
			$message['create_time'] 	=	date('Y-m-d H:i:s',$message['create_time']);
		}

		// 开始赋值
		$this->assign('message',$message);
		$this->assign('type',$type);

		// 不同 type  来展示不同模板
		if($type == 'administer'){
			return view('../template/admin/user/showadminister.html');
		}else{
			return view();
		}
		
	}
	/*
		*	用户修改信息
		*	必须传入变量 ： $type => 类型(user->普通用户 , administer->管理员)
		*				   $id 	 => 用户id(user_id) 或者 管理员id(admin_id)
	*/
	public function edit($type,$id){
		if($type == 'user'){
			$sqlTop 	=	'BookUsers';
		}elseif($type == 'administer'){
			$sqlTop 	=	'BookAdmins';
		}else{
			return json(['code'=>0,'msg'=>'未定义']);
		}

		// 获取用户信息
		$message 	=	Model($sqlTop)->get($id)->toArray();

		// 赋值
		$this->assign('type',$type);
		$this->assign('message',$message);

		// 根据 type 来赋值不同模板
		if($type == 'administer'){
			return view('../template/admin/user/editadminister.html');
		}else{
			return view();
		}
	}
	/*
		*	修改用户信息处理
	*/
	public function editadmin(){
		$data  = input('post.');

		// 判断type
		switch ($data['type']) {
			case 'user':
				// 删除type
				unset($data['type']);

				// 赋值并删除id
				$id 	=	$data['id'];
				unset($data['id']);

				// 遍历哪项为空则删除哪项
				foreach ($data as $key => $value) {
					if(empty($data[$key])){
						unset($data[$key]);
					}
				}

				// 开始判断修改后的用户名是否重复
					// 查找用此用户名的数量
					$findUsername 	=	Model('BookUsers')->where('user_id',$id)->find();
					if($findUsername){
						$findUsername 	=	$findUsername->toArray();
						// 对比如果id不一样 则为数据库内有此数据 
						if($findUsername['user_id'] != $id){
							return json(['code'=>0,'msg'=>'数据库内已经有此用户']);
						}
					}
				// 开始更新
				$update 	=	Model('BookUsers')->where('user_id',$id)->update($data);

				if($update){
					return json(['code'=>1,'修改成功']);
				}else{
					return json(['code'=>0,'修改失败']);
				}

			break;
			
			case 'administer':

			// dump($data);exit;
				// 检测用户名是否存在
					//	搜索数据库内用此用户名的用户
					$findUsername 	=	Model('BookAdmins')->where('username',$data['username'])->find();

					//	如果有 判断id是否相等 如果不相等 则存在此用户名
					if($findUsername){
						$findUsername 	=	$findUsername->toArray();
						// 判断id
						if($data['id'] != $findUsername['admin_id']){
							return json(['code'=>0,'msg'=>'用户名已经存在']);
						}
					}

				// 判断password 是否为空 如果为空删除 不为空MD5加密
				if(empty($data['password'])){
					unset($data['password']);
				}else{
					$data['password'] = md5($data['password']);
				}

				// 删除type 
				if(isset($data['type'])){
					unset($data['type']);
				}

				// 提取id并删除
				if(isset($data['id'])){
					$id 	=	$data['id'];
					unset($data['id']);
				}else{
					return json(['code'=>0,'msg'=>'缺少必要参数ID']);
				}

				// 遍历 空值删除
				foreach ($data as $key => $value) {
					if(empty($data[$key])){
						unset($data[$key]);
					}
				}

				// 开始更新
				$update = Model('BookAdmins')->where('admin_id',$id)->update($data);

				if($update){
					// 判断修改的id是否等于 当前session ID  如果等于 则删除当前session
					if($id == Session('admin_id')['admin_id']){
						Session('admin_id',null);
					}
					return json(['code'=>1,'msg'=>'修改成功']);
				}else{
					return json(['code'=>0,'msg'=>'修改失败，可能是未作修改']);
				}

			break;
			// type 错误则参数错误
			default:
				return json(['code'=>0,'msg'=>'参数错误']);
			break;
		}
	}
	/*
		*	删除用户
	*/
	public function del(){

	}
	/*
		*	修改用户状态
	*/
	public function status($type,$id){
		if($type == 'user'){
			$lastStatusArr = Model('BookUsers')->get($id)->toArray();
		}else if($type == 'administer'){
			$lastStatusArr = Model('BookAdmins')->get($id)->toArray();
		}else{
			return json(['code'=>0,'msg'=>'type参数错误']);
		}

		$lastStatus = $lastStatusArr['status'];

		if($lastStatus == 0){
			$newStatus = 1;
		}else{
			$newStatus = 0;
		}

		// 开始更改
		if($type == 'user'){
			$update = Model('BookUsers')->where('user_id',$id)->update(['status'=>$newStatus]);
		}elseif($type == 'administer'){
			$update = Model('BookAdmins')->where('admin_id',$id)->update(['status'=>$newStatus]);
		}else{
			return json(['code'=>0,'msg'=>'更新时遇到type参数错误']);
		}

		// 判断状态
		if($update){
			// 判断如果修改 id等于当前session id 清除session
			if($type == 'administer'){
				if($id == Session('admin_id')['admin_id']){
					Session('admin_id',null);
				}
			}
			return json(['code'=>1,'msg'=>'更新状态成功']);
		}else{
			return json(['code'=>0,'msg'=>'更新状态失败']);
		}
	}
}