<?php
namespace app\index\controller;


use think\Controller;
use think\Model;
use think\Session;
use think\Cookie;

/*
	*	前台用户登录
*/

class Login extends Controller {
	public function _initialize(){
		// 获取公共参数
		$commonData 	=	getCommonData();

		$this->assign('commonData',$commonData);
	}
	public function index(){
		// 如果有有session -> user_id则跳转到用户中心
		if(Session::has('user_info')){
			$this->redirect(url('index/user/index'));
		}

		// 获取meta
		$meta 	=	getMeta('login');

		$this->assign('meta',$meta);
		return view();
	}
	/*
		登录验证
    */
	public function login_check(){
		$data  = input('post.');

		// 如果没有数据 返回error
		if(empty($data)){
			$this->error('数据不能为空');
		}

		// 过滤
		foreach ($data as $key => $value) {
			$data[$key]	=	htmlspecialchars($data[$key]);
		}


		// 放入变量
		$username 	=	$data['username'];
		$userpass	=	$data['password'];
		$yzm		=	$data['yzm'];

		// 验证验证码是否正确
		if(!captcha_check($yzm)){
			return $this->error('验证码错误');
		}

		// 查找是否有此用户名(用户名唯一)
		$findUserName	=	Model('BookUsers')
								->where('username',$username)
								->whereOr('bbsname',$username)
								->whereOr('phone',$username)
								->whereOr('email',$username)
								->find();
		
		// 如果有此用户 验证密码是否正确
		if($findUserName){
			$findUserName 	=	$findUserName->toArray();
			if($findUserName['password'] == md5($userpass)){
				// 设置session
				Session::set('user_info',$findUserName);

				return $this->success('登录成功');
			}else{
				return $this->error('密码错误');
			}
		}else{
			return $this->error('没有此用户');
		}
	}
	public function reg(){
		// 如果有session则跳转到用户中心
		if(Session::has('user_id')){
			header('location='.url('index/user/index'));
		}

		// 获取meta
		$meta 		=	getMeta('reg','','','');

		$this->assign('meta',$meta);
		return view();
	}
	/*	注册验证	*/
	public function reg_check(){
		$data = input('post.');
		if(!$data){
			return $this->error('参数不完整');
		}

		// 过滤参数
		foreach ($data as $key => $value) {
			$data[$key] 	=	htmlspecialchars($data[$key]);
		}

		// 判断用户名与邮箱数据库内是否有
		$findUserName 	= 	Model('BookUsers')->where('username',$data['username'])->find();
		$findBbsName	=	Model('BookUsers')->where('bbsname',$data['bbsname'])->find();
		$findEmail		=	Model('BookUsers')->where('email',$data['email'])->find();

		if($findUserName){
			return $this->error('用户名已经存在(英文)');
		}
		if($findBbsName){
			return $this->error('用户名已经存在(中文)');

		}
		if ($findEmail) {
			return $this->error('邮箱已经存在');
		}

		// 判断长度
		if(strlen($data['username']) > 20 || strlen($data['username']) < 3) {
			return $this->error('用户名(英文)长度不符');
		}

		if(!preg_match("/^[a-zA-Z0-9]+$/", $data['username'])){
			return $this->error('英文用户名只能为英文字母或者数字');
		}

		if(mb_strlen($data['bbsname'] > 20) ||strlen($data['bbsname']) < 5) {
			return $this->error('用户名(中文)长度不符');

		}

		if($data['onePass'] != $data['twoPass']){
			return $this->error('第一次与第二次密码不相同');
		}

		if(strlen($data['onePass']) < 5){
			return $this->error('密码长度过短');
		}

		if(!preg_match("/^[a-zA-Z0-9]+$/", $data['onePass'])){
			return $this->error('密码只能为英文字母或者数字');
		}

		if($data['email-yzm'] != setcookie('email-yzm')){
			return $this->error('验证码错误');
		}

		// 开始组合数据并插入
		$insertData = [
			'username'	=>	$data['username'],
			'bbsname'	=>	$data['bbsname'],
			'password'	=>	md5($data['onePass']),
			'email'		=>	$data['email'],
			'sex'		=>	$data['sex'],
			'reg_time'	=>	@time(),
		];

		$insert = Model('BookUsers')->insert($insertData);
		if($insert){
			return $this->success('注册成功');
		}else{
			return $this->error('注册失败');
		}
			
	}
	/*	发送验证码	*/
	public function send_mail(){
		$data = input('add');
		if(!$data || empty($data)){
			return $this->error('参数不完整');
		}else{
			// 接受参数 赋值收件人
			$add 			=	$data;
			// 解密
			$add			=	base64_decode($add);
		}

		// 生成随机码
		$safeNumber = rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);

		// 自定义发送标题
		$sendTitle 		= 	'您的验证码为'.$safeNumber;
		// 自定义发送内容
		$sendContent	=	'尊敬的用户您好，您的验证码为<b style="color:red;">'.$safeNumber.'</b>';
		// 自定义发件人名称
		$sendName 		=	'简易书城';
		// 自定义发件人邮箱
		$sendMail 		=	'2280120391@qq.com';

		// 开始调用函数来发送邮件
		$sendMailBeging = 	sendMail($sendName,$sendMail,$sendTitle,$sendContent,$add);

		// 如果发送成功，将验证码写入cookie 并提示验证码
		if($sendMailBeging){
			// 设置cookie
			Cookie::set('yzm',$safeNumber);
			return $this->success($safeNumber);
			die();
		}else{
			return $this->error('发送失败系统错误');
		}

	}
	/*	找回密码	*/
	public function back_password(){
		return view();
	}
	/*	退出登录	*/
	public function login_out(){

		// 删除作用域
		Session::delete('user_info');
		Cookie::delete('user_info');

		// 成功后跳转到首页
		$this->redirect(url('index/index/index'));
	}
	/*	用户锁定后显示此界面	*/
	public function lock(){
		return view();
	}
}
?>