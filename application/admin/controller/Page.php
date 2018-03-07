<?php
namespace app\admin\controller;

use think\Model;
use think\Session;
use think\Controller;

/*
	*	页面管理模块
*/

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
	/*
		*	插入新页面
	*/
	public function new_page(){
		$data 	=	input('post.');
		// 没有标题不成立
		if(!$data['title']){
			return $this->error('标题不能为空');
		}


		// 插入时间参数 
		$data['create_time'] 	=	time();

		// 插入创建id
		$data['create_id'] 		=	Session('admin_id')['admin_id'];

		// 添加status
		$data['status'] 		=	1;

		// 开始插入
		$insert 	=	Model('BookPages')->insert($data);
		if($insert){
			return $this->success('插入成功');
		}else{
			return $this->error('插入失败');
		}
	}
	/*
		*	删除
	*/
	public function del(){
		$id 	=	input('id');
		if(!$id){
			return $this->error('没有此id');
		}

		// 开始删除
		$del	=	Model('BookPages')->where('page_id',$id)->delete();

		if($del){
			return $this->success('删除成功');
		}else{
			return $this->error('删除失败');
		}
	}
	/*
		*	编辑
	*/
	public function edit(){
		$id 	=	input('id');
		if(!$id){
			$this->error('没有id');
		}

		$pageData 	=	Model('BookPages')->get($id)->toArray();

		// 反转html
		$pageData['content'] 	=	htmlspecialchars_decode($pageData['content']);

		$this->assign('pageData',$pageData);

		return view();
	}
	/*

	*/
	public function edit_admin(){
		$data 	=	input('post.');
		// 开始更新
		$updates 	=	Model('BookPages')->where('page_id',$data['page_id'])->update($data);

		if($updates){
			return $this->success('修改成功');
		}else{
			return $this->error('修改失败');
		}
	}
}
