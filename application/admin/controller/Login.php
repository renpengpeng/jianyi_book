<?php
namespace app\admin\controller;

use think\Controller;
use think\Model;
use think\Session;
use think\Cookie;

class Login extends Controller {
	// 自动加载 如果有session admin_id 则跳转到首页
	public function _initialize(){
		if(Session::has('admin_id')){
			$this->redirect(url('admin/index/index'));
		}
	}
	public function index(){
		return view();
	}
	// 登录验证
	public function check(){
		$data = input('post.');

		// 判断如果参数缺失
		if(!isset($data['username']) || !isset($data['password'])){
			return json(['code'=>0,'msg'=>'请填写完整']);
		}

		// 过滤html敏感字符串
		foreach ($data as $key => $value) {
			$data[$key] = htmlspecialchars($data[$key]);
		}

		// 赋值
		$username = $data['username'];
		$password = md5($data['password']);

		// 判断用户名是否存在
		$usernameHas	=	Model('BookAdmins')->where('username',$username)->find();

		if($usernameHas){
			// 转换数组
			$usernameHas = $usernameHas->toArray();

			// 判断状态
			if($usernameHas['status'] == 0){
				return json(['code'=>0,'msg'=>'用户不可用']);exit;
			}

			// 对比密码
			if($usernameHas['password'] == $password){
				// 设置session
				Session::set('admin_id',$usernameHas);
				return json(['code'=>1,'msg'=>'登录成功']);
			}else{
				return json(['code'=>0,'msg'=>'密码错误']);
			}

		}else{
			return json(['code'=>0,'msg'=>'用户不存在']);
		}
	}

}

?>