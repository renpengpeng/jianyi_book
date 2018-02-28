<?php
namespace app\index\controller;

use think\Controller;


class Index extends Controller
{
    public function index()
    {
    	// 获取meta
    	$meta 		=	getMeta();
    	// 获取所有商品分类
    	$allCate 	=	getCateForIndexA();

    	$this->assign('allCate',$allCate);
    	$this->assign('meta',$meta);

    	return view();
    }
    /*
		*	提示信息
    */
	public function cavaet($msg){
		// 获取meta
		$meta 			=	getMeta('cavaet','',3,$msg);
		// 获取所有分类
		$allCate 		=	getCateForIndexAllA();


		$this->assign('msg',$msg);
		$this->assign('meta',$meta);
		$this->assign('allCate',$allCate);

		return view();
	}
	
   
}
