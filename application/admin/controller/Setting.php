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
		}

		// 开始更新
		$update 	=	Model('BookSetting')->where('setting_id',1)->update($data);

		if($update){
			return json(['code'=>1,'msg'=>'修改成功']);
		}else{
			return json(['code'=>0,'msg'=>'修改失败']);
		}
	}
}

?>