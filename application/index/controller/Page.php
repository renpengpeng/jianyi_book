<?php
namespace app\index\controller;

use think\Model;
use think\Session;
use think\Controller;

class Page extends Controller {
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

		// 获取header的所有分类
		$allCate 	=	getCateForIndexA();

		$this->assign('pageData',$pageData);
		$this->assign('meta',$meta);
		$this->assign('allCate',$allCate);

		return view();
		
	}
}

?>