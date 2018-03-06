<?php
namespace app\admin\controller;

use think\Model;
use think\Controller;
use think\Session;
use think\Cookie;

class Comment extends Controller {
	public function _initialize(){
		// 检测如果没有admin_id 跳转到登录界面
		if(!Session::has('admin_id')){
			$this->redirect(url('admin/login/index'));
		}
	}
	public function index(){
		// setting
		$setting 	=	getSetting();
		// 获取展示数量
		$showNum 	=	$setting['admin_list_show_num'];

		// 获取page
		if(!input('page')){
			$page 	=	1;
		}else{
			$page 	=	input('page');
			if(!is_numeric($page)){
				$this->redirect(url('admin/index/cavaet',['msg'=>'页码不符合规范']));
			}else{
				// 统计评论数量
				$commentCount 	=	Model('BookComment')->count();
				// 如果页码超出限制跳转
				if(ceil($commentCount/$showNum) < $page){
					$this->redirect(url('admin/index/cavaet',['msg'=>'页码超出限制']));
				}
			}
		}

		// 获取所有评论
		$commentData 	=	Model('BookComment')->order('comment_id desc')->page($page)->limit($showNum)->select();
		if($commentData){
			$commentData 	=	$commentData->toArray();

			foreach ($commentData as $key => $value) {
				// 获取用户昵称
				$userArr 	=	Model('BookUsers')->get($commentData[$key]['user_id']);
				if($userArr){
					$userArr 	=	$userArr->toArray();
					$commentData[$key]['username'] 	=	$userArr['bbsname'];
				}else{
					$commentData[$key]['username']	=	'用户已注销';
				}

				// 获取商品名称
				$shopArr 	=	Model('BookGoods')->get($commentData[$key]['good_id']);
				if($shopArr){
					$shopArr	=	$shopArr->toArray();
					$commentData[$key]['good_name'] 	=	$shopArr['title'];
				}else{
					$commentData[$key]['good_name'] 	=	'商品已删除';
				}
			}
		}

		// 分页
		$pageination 	=	Model('BookComment')->paginate($showNum);

		$this->assign('commentData',$commentData);
		$this->assign('pageination',$pageination);

		return view();
	}
	/*
		*	删除商品
	*/
	public function del(){
		$id 	=	input('id');
		if(!$id){
			return $this->error('参数缺少');
		}
		// 开始获取评论数据
		$commentData 	=	Model('BookComment')->get($id);
		if(!$commentData){
			return $this->error('没有此评论');
		}else{
			$commentData 	=	$commentData->toArray();
		}

		// 开始删除
		$del 	=	Model('BookComment')->where('comment_id',$id)->delete();
		if($del){
			return $this->success('删除成功');
		}else{
			return $this->error('删除失败');
		}

	}
}