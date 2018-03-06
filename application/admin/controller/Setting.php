<?php
namespace app\admin\controller;

use think\Model;
use think\Controller;
use think\Session;
use think\Cookie;

class Setting extends Controller {
	public function _initialize(){
		// 检测如果没有admin_id 跳转到登录界面
		if(!Session::has('admin_id')){
			$this->redirect(url('admin/login/index'));
		}else{
			$adminSession 	=	Session('admin_id');

			// 如果权限为 运营团队 禁止进入
			if($adminSession['permissions'] == 2){
				$this->redirect(url('admin/index/cavaet',['msg'=>'没有足够的权限']));
			}
		}

	}
	public function index(){
		// 获取setting
		$setting 	=	getSetting();

		// 格式化时间戳
		$setting['web_create_time'] 	=	date('Y-m-d H:i:s',$setting['web_create_time']);

		// 上传字节转换
		$setting['upload_pic_big_size'] 	=	ceil($setting['upload_pic_big_size']/1048576);

		// 反转html
		foreach ($setting as $key => $value) {
			$setting[$key]  =	htmlspecialchars_decode($setting[$key]);
		}

		$this->assign('setting',$setting);

		return view();
	}
	/*
		*	修改
	*/
	public function modify(){
		$data 	=	input('post.');
		if(!isset($data) || empty($data)){
			return json(['code'=>0,'msg'=>'缺少必要参数']);
		}else{
			foreach ($data as $key => $value) {
				$data[$key] 	=	htmlspecialchars($data[$key]);
			}
		}

		// 格式化时间戳
		if(isset($data['web_create_time'])){
			$data['web_create_time'] 	=	strtotime($data['web_create_time']);
		}

		// 转换字节
		if(isset($data['upload_pic_big_size'])){
			$data['upload_pic_big_size'] 	=	$data['upload_pic_big_size'] * 1048576;
		}
		
		// 去除空元素
		foreach ($data as $key => $value) {
			if(empty($data[$key])){
				unset($data[$key]);
			}
		}

		// 开始更新
		$update 	=	Model('BookSetting')->where('setting_id',1)->fetchSql(false)->update($data);


		if($update){
			 $this->success('修改成功');
		}else{
			$this->error('修改失败');
		}
	}
}

?>