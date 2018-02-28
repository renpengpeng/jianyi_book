<?php
namespace app\index\controller;

use think\Controller;
use think\Model;
use think\Session;
use think\Cookie;

class Book extends Controller {
	public function index($id){
		// 判断id是否为数字不是数字则重定向到提示页面
		if(!is_numeric($id)){
			$this->redirect(url('index/index/cavaet',['msg'=>'商品参数错误']));
		}else{
			// 判断数据库内是否有此id
			$hasData 		=	Model('BookGoods')->get($id);

			if(!$hasData){
				$this->redirect(url('index/index/cavaet',['msg'=>'商品参数错误']));
			}else{
				$hasData 	=	$hasData->toArray();
			}
		}

		// 反转换html
		$hasData['details'] 	=	htmlspecialchars_decode($hasData['details']);

		// 转换时间戳
		if(!empty($hasData['time'])){
			$hasData['time'] 	=	date("Y-m-d H:i",$hasData['time']);
		}

		// 分割副图x4并向数组添加：pic1,pic2,pic3,pic4,pic5
		$fuPicArr 	=	explode(',', $hasData['vice_img']);

		// 替换null为''
		foreach ($fuPicArr as $key => $value) {
			if($fuPicArr[$key] == 'null'){
				$fuPicArr[$key] 	=	'';
			}
		}
		
		$hasData['pic1'] 			=	$hasData['main_img'];
		$hasData['pic2']			=	$fuPicArr[0];
		$hasData['pic3']			=	$fuPicArr[1];
		$hasData['pic4']			=	$fuPicArr[2];
		$hasData['pic5']			=	$fuPicArr[3];

		// 获取meta
		$meta 			=	getMeta();
		// 获取所有分类(header)
		$allCate 		=	getCateForIndexA();
		// 获取侧边栏数据
		$sidebarData 	=	getListSidebar();

		$this->assign('meta',$meta);
		$this->assign('allCate',$allCate);
		$this->assign('sidebarData',$sidebarData);
		$this->assign('shopData',$hasData);
		return view();
	}
}

?>