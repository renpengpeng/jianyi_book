<?php
namespace app\index\controller;

use think\Model;
use think\Controller;
use think\Session;
use think\Cookie;

class Lists extends Controller {
	/*
		*	如果id = all 显示所有分类
	*/
	public function index($id='all'){
		// 获取setting
		$setting 			=	getSetting();
		// 赋值前台首页每页展示多少数量
		$indexListShowNum 	=	$setting['index_list_show_num'];

		if($id == 'all'){
			// 获取所有分类(header)
			$allCate 		=	getCateForIndexA();

			// 获取所有分类
			$getAllCate 	=	getCateForIndexAllA();

			// 获取meta
			$meta 			=	getMeta('listall','','');


			$this->assign('allCate',$allCate);
			$this->assign('getAllCate',$getAllCate);
			$this->assign('meta',$meta);

			return view('../template/index/lists/all.html');
		}else{
			// 检测是否为数字
			if(!is_numeric($id)){
				$this->redirect(url('index/index/cavaet',['msg'=>'参数错误']));
			}else{
				// 判断是否有此ID
				$isId 	=	Model('BookCates')->get($id);

				if(!$isId){
					$this->redirect(url('index/index/cavaet',['msg'=>'参数错误']));
				}
			}

			// 检测page
			if(!input('page')){
				$page 	=	1;
			}else{
				$page 	=	htmlspecialchars(input('page'));
				// 开始判断是否超出限制

					// 统计总数量
					$allCount 	=	Model('BookGoods')->where('status',1)->where('cate',$id)->count();

					if($allCount==0 && $page != 1){
						$this->redirect(url('index/index/cavaet',['msg'=>'页数超出限制']));
					}else{
						if(@ceil($allCount/$indexListShowNum) < $page){
							$this->redirect(url('index/index/cavaet',['msg'=>'页数超出限制']));
						}
					}		
			}
			// 获取meta
			$meta 			=	getMeta('list',$id,'','');

			// 获取分类基本信息
			$cateData 		=	Model('BookCates')->get($id);

			// 获取所有分类header
			$allCate 		=	getCateForIndexA();

			// 获取侧边栏数据
			$sidebarData 	=	getListSidebar();

			// 获取下级分类
			$nextID 		=	getCateNextAll($id);

			// dump($nextID);exit;
			if(!$nextID){
				// 根据ID获取商品数据
				$shopData 		=	Model('BookGoods')
										->order('good_id desc')
										->where([
											'cate' 		=>	$id,
											'status' 	=>	1
										])
										->page($page)
										->limit($indexListShowNum)
										->select();

				// 如果查询成功转换数组并统计数量
				if($shopData){
					$shopCount	=	$shopData->toArray();
					$shopCount 	=	count($shopCount);
				}else{
					$shopCount 	=	0;
				}

				// 分页
				$pageination 	=	Model('BookGoods')
										->order('good_id desc')
										->where([
											'cate' 		=>	$id,
											'status' 	=>	1
										])
										->paginate($indexListShowNum);
			}else{
				// 定义查询条件
				$selectTJ	=	[
					'cate' 		=>	['in',$nextID],
					'status' 	=>	['eq',1],
				];
				$shopData 		=	Model('BookGoods')
										->order('good_id desc')
										->where($selectTJ)
										->limit($indexListShowNum)
										->page($page)
										->select();

				// 如果查询成功转换数组并统计数量
				if($shopData){
					$shopCount	=	$shopData->toArray();
					$shopCount 	=	count($shopCount);
				}else{
					$shopCount 	=	0;
				}

				// 分页
				$pageination  	=	Model('BookGoods')
										->order('good_id desc')
										->where($selectTJ)
										->paginate($indexListShowNum);
			}

			$this->assign('meta',$meta);
			$this->assign('allCate',$allCate);
			$this->assign('sidebarData',$sidebarData);
			$this->assign('shopData',$shopData);
			$this->assign('pageination',$pageination);
			$this->assign('shopCount',$shopCount);
			$this->assign('cateData',$cateData);
			return view();
		}
	}
	public function test(){
		
	}

}
?>