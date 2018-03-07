<?php
namespace app\index\controller;

use think\Model;
use think\Session;
use think\Cookie;
use think\Controller;

/*
	*	商品搜索
*/

class Search extends Controller {
	public function _initialize(){
		 // 获取公共 参数
        $commonData     =   getCommonData();

        $this->assign('commonData',$commonData);
	}
	public function index(){
		if(!input('?keywords')){
			$this->redirect(url('index/index/cavaet',['msg'=>'参数缺少']));
		}else{
			$keywords 	=	htmlspecialchars(input('keywords'));
		}

		// 判断page
		if(!input('?page')){
			$page 	=	1;
		}else{
			$page 	=	input('page');
			if(!is_numeric($page)){
				$this->redirect(url('index/index/cavaet',['msg'=>'page只能为数字']));
			}
		}

		// 获取setting
		$setting 		=	getSetting();
		$indexListShowNum 	=	$setting['index_list_show_num'];

		// 开始搜索
		$search 		=	Model('BookGoods')
								->where('title','like',"%{$keywords}%")
								->page($page)
								->limit($indexListShowNum)
								->select();

		if($search){
			// 转换数组
			$search 		=	$search->toArray();
			// 统计数据库内总数量
			$goodCount 		=	Model('BookGoods')->count();
			// 获取搜索到的数量
			$searchCount	= 	count($search);
			if($page > 1){
				// 判断页数是否超出县支行
				if(ceil($goodCount / $indexListShowNum) < $page){
					$this->redirect(url('index/index/cavaet',['msg'=>'超出限制的page']));
				}
			}
		}else{
			$searchCount 	= 	0;
		}

		// 分页
		$pageination 	=	Model('BookGoods')
								->where('title','like',"%{$keywords}%")
								->paginate($indexListShowNum,[
									'query' 	=> 	['keywords'=>$keywords]
								]);

		// 侧边栏数据
		$sidebarData 	=	getListSidebar();

		// 获取meta数据
		$meta 			=	getMeta('search','','',$keywords);

		$this->assign('meta',$meta);
		$this->assign('searchData',$search);
		$this->assign('pageination',$pageination);
		$this->assign('sidebarData',$sidebarData);
		$this->assign('searchCount',$searchCount);
		$this->assign('keywords',$keywords);

		return view();
	}
}