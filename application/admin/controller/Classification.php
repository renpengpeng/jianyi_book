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
		}else{
			$adminSession 	=	Session('admin_id');

			// 运营团队不允许修改分类
			if($adminSession['permissions'] == 2){
				$this->redirect(url('admin/index/cavaet',['msg'=>'没有足够的权限']));
			}
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

			$garden 	= $lastGarden['garden']+1;
		}

		// 整理插入数据
		$insertData	=	[
			'cate_name'		=>	$data['catename'],
			'pid'			=>	$data['pid'],
			'garden'		=>	$garden,
			'keywords' 		=>	$data['keywords'],
			'description' 	=>	$data['description']
		];


		// 开始插入
		$insertBeign		=	Model('BookCates')->insert($insertData);

		if($insertBeign){
			return $this->success('增加分类成功');
		}else{
			return $this->error('增加分类失败');
		}

	}
	/*
		*	删除方法
	*/
	public function del($id){
		if(!$id || empty($id)){
			return $this->error('参数缺少');
		}

		// 检测是否有子分类
		$hasZi	=	getCateNextAll($id);

		if($hasZi){
			// 遍历数组各个删除
				$map['cate_id']	=	array('in',$hasZi);
				$del 			=	Model('BookCates')->where($map)->delete();
		}else{
				$del 			=	Model('BookCates')->where('cate_id',$id)->delete();
		}

		if(!$del){
			return $this->error('删除分类成功');
		}else{
			return $this0->success('删除分类失败');
		}


	}
	/*
		*	分类编辑
	*/
	public function edit($id){
		// 检测如果没有post 或者 没有$id 
		if(!$id){
			return $this->error('参数缺少');
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
			$lastCateId			=	$data['last_id'];
			// 分类名称
			$cateName 			=	$data['cate_name'];
			// 接收分类id
			$cateId 			=	$data['cate_id'];
			// 接收关键词
			$cateKeywords 		=	$data['keywords'];
			$cateDescription 	=	$data['description'];

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
				'graden'	=>	$nowCateGraden,
				'keywords' 	=>	$cateKeywords,
				'description'	=>	$cateDescription
			];
		}else{
			// 只修改分类名称 * 重组修改数据
			$updateData			=	[
				'cate_name'=>$cateName,
				'keywords' 	=>	$cateKeywords,
				'description'	=>	$cateDescription
			];

		}

		// 开始更新
		$updateBegin		=	Model('BookCates')->where('cate_id',$cateId)->update($updateData);

		// 返回数据
		if($updateBegin){
			return $this->success('更新成功');
		}else{
			return $this->error('更新失败');
		}


	}

}

?>