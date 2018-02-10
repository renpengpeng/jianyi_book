<?php
namespace app\admin\controller;

use think\Session;
use think\Cookie;
use think\Model;
use think\Controller;

/*
	*	分类管理
*/

class Classification extends Controller {
	// 自动加载， 如果没有 session admin_id 跳转到登录页面
	public function _initialize(){
		if(!Session::has('admin_id')){
			$this->redirect(url('admin/login/index'));
		}
	}
	// 首页 展示所有分类
	public function index(){
		// 获取所有分类
		$allCate	=	adminGetCateForLi();

		// 赋值所有分类
		$this->assign('allCate',$allCate);

		// option分类
		$cateOption	=	adminGetCateForOption();

		// 赋值option 分类
		$this->assign('cateOption',$cateOption);

		return view();
	}
	// 添加分类
	public function create_classification(){
		// 获取数据
		$data = input('post.');

		// 判断是否有分类名称和上级id
		if(!isset($data['pid']) || !isset($data['catename'])){
			return json(['code'=>0,'msg'=>'参数缺少']);
		}else{
			foreach ($data as $key => $value) {
				$data[$key]	=	htmlspecialchars($data[$key]);
			}
		}

		// 检测上级的等级
		if($data['pid'] == 0){
			$garden = 1;
		}else{
			// 检测 上级等级 设置本级等级为上级等级+1
			$lastGarden = Model('BookCates')->where('cate_id',$data['pid'])->find()->toArray();

			$garden = $lastGarden['garden']+1;
		}

		// 整理插入数据
		$insertData	=	[
			'cate_name'	=>	$data['catename'],
			'pid'		=>	$data['pid'],
			'garden'	=>	$garden
		];


		// 开始插入
		$insertBeign	=	Model('BookCates')->insert($insertData);

		if($insertBeign){
			return json(['code'=>1,'msg'=>'插入成功']);
		}else{
			return json(['code'=>0,'msg'=>'插入失败']);
		}

	}

}

?>