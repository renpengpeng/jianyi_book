<?php
namespace app\index\controller;

use think\Model;
use think\Session;
use think\Controller;

class Page extends Controller {
	public function _initialize(){
		// 获取公共参数
		$commonData 	=	getCommonData();

		$this->assign('commonData',$commonData);
	}
	public function index($id){
		// 如果page不是数字则跳转错误页面 否则获取page信息
		if(!is_numeric($id)){
			$this->redirect(url('index/index/cavaet',['msg'=>'非法访问的页面']));
		}else{
			$pageData 	=	Model('BookPages')->get($id);
			// 如果获取page失败跳转错误页面
			if(!$pageData){
				$this->redirect(url('index/index/cavaet',['msg'=>'没有此页面']));
			}else{
				$pageData 	=	$pageData->toArray();
			}
		}

		// 如果页面的status为0 检测是否存在admin_info session 
		// 如果存在则正常浏览否则跳转错误页面
		if($pageData['status'] == 0){
			if(!Session::has('admin_id')){
				$this->redirect(url('index/index/cavaet',['msg'=>'未开放的页面']));
			}
		}

		// 根据创建人id获取创建人昵称
		$createUser 	=	Model('BookAdmins')->get($pageData['create_id']);
		if($createUser){
			$createUser 	=	$createUser->toArray();
			$pageData['create_name'] 	= 	$createUser['bbsname'];	
		}else{
			$pageData['create_name'] 	= 	'用户已注销';	
		}

		// 反转html  
		$pageData['content'] 	=	htmlspecialchars_decode($pageData['content']);

		// 获取meta
		$meta 	=	getMeta('page',$id,'','');

		// 浏览量自增1
		$zeng 	=	Model('BookPages')->where('page_id',$id)->setInc('views');

		$this->assign('pageData',$pageData);
		$this->assign('meta',$meta);

		return view();
		
	}
	/*
		*	友情链接页面
	*/
	public function links(){
		// 获取meta
		$meta 	=	getMeta();

		// 获取友情链接
		$linkData 	=	Model('BookLinks')->where('status',1)->order('link_id desc')->select();

		$this->assign('meta',$meta);
		$this->assign('linkData',$linkData);
		return view();
	}
}

?>