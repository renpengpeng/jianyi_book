<?php
namespace app\admin\controller;

use think\Model;
use think\Controller;
use think\Session;
use think\Cookie;

class User extends Controller {
	public function _initialize(){

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
		*	修改用户信息处理文件
	*/
	public function editadmin(){
		$data  = input('post.');

		dump($data);
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
			return json(['code'=>1,'msg'=>'更新状态成功']);
		}else{
			return json(['code'=>0,'msg'=>'更新状态失败']);
		}
	}
}