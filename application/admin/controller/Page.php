<?php
namespace app\admin\controller;

use think\Model;
use think\Session;
use think\Controller;

class Page extends Controller{
	// 自动加载 如果没有session : admin_id 跳转到登录页面
	public function _initialize(){
		if(!Session::has('admin_id')){
			$this->redirect(url('admin/login/index'));
		}else{
			$adminSession 	=	Session('admin_id');
		}
	}
	public function index(){
		// 获取setting
		$setting 	=	getSetting();
		$adminListShowNum 	=	$setting['admin_list_show_num'];
		// 获取page
		if(!input('page')){
			$page 	=	1;
		}else{
			$page 	=	input('page');
			if(!is_numeric($page)){
				$this->redirect(url('admin/index/cavaet',['msg'=>'页码应该为数字']));
			}else{
				// 计算总数量
				$allCount 	=	Model('BookPages')->count();
				if(ceil($allCount/$adminListShowNum) < $page){
					$this->redirect(url('admin/index/cavaet',['msg'=>'页码超出限制']));
				}
			}
		}

		// 获取数据
		$pageData 	=	Model('BookPages')
							->order('page_id desc')
							->page($page)
							->limit($adminListShowNum)
							->select();
		// 分页
		$pageination 	=	Model('BookPages')->paginate($adminListShowNum);


		$this->assign('pageData',$pageData);
		$this->assign('pageination',$pageination);

		return view();
	}
}