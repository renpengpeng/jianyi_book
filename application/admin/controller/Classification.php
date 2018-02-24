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
	/*
		*	删除方法
	*/
	public function del($id){
		if(!$id || empty($id)){
			return json(['code'=>'0','msg'=>'参数不完整']);
		}

		// 检测是否有子分类
		$hasZi	=	getCateNextAll($id);

		if($hasZi){
			// 遍历数组各个删除
				$map['cate_id']	=	array('in',$hasZi);
				$del 			=	Model('BookCates')->where($map)->delete();
		}else{
				$del 	=	Model('BookCates')->where('cate_id',$id)->delete();
		}

		if(!$del){
			return json(['code'=>'0','msg'=>'删除失败']);
		}else{
			return json(['code'=>'1','msg'=>'删除成功']);
		}


	}
	/*
		*	分类编辑
	*/
	public function edit($id=null){
		// 检测如果没有post 或者 没有$id 
		if(!$id){
			return json(['code'=>0,'msg'=>'信息缺少']);
		}

		// 如果有id
			// 获取分类信息 
			$cateInfo		=	Model('BookCates')->get($id)->toArray();
			// 获取分类option
			$cateOption 	=	adminGetCateForOption();

			// 赋值信息
			$this->assign('cateInfo',$cateInfo);
			$this->assign('cateOption',$cateOption);

			// 返回页面
			return view();


	}
	/*
		*	分类编辑处理
	*/
	public function edit_admin(){
		$data 	=	input('post.');
		if(!$data || empty($data)){
			return json(['code'=>0,'msg'=>'参数缺少']);
		}

		// 开始接收
			// 上级分类id
			$lastCateId			=	$data['last-id'];
			// 分类名称
			$cateName 			=	$data['cate-name'];
			// 接收分类id
			$cateId 			=	$data['cate-id'];

		// 如果上级分类为0 
		if($lastCateId != 0){
			// 查询上级分类的等级
			$lastCateGradenArr	=	Model('BookCates')->get($lastCateId)->toArray();
			$lastCateGraden 	=	$lastCateGradenArr['graden'];

			// 现在的graden为上级graden+1
			$nowCateGraden 		=	$lastCateGraden+1;

			// 插入数据重组
			$updateData 		=	[
				'cate_name' =>	$cateName,
				'pid'		=>	$lastCateId,
				'graden'	=>	$nowCateGraden
			];
		}else{
			// 只修改分类名称 * 重组修改数据
			$updateData			=	['cate_name'=>$cateName];

		}

		// 开始更新
		$updateBegin		=	Model('BookCates')->where('cate_id',$cateId)->update($updateData);

		// 返回数据
		if($updateBegin){
			return json(['code'=>1,'msg'=>'更新成功']);
		}else{
			return json(['code'=>0,'msg'=>'更新失败']);
		}


	}

}

?>