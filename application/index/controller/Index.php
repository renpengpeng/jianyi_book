<?php
namespace app\index\controller;

use think\Controller;


class Index extends Controller
{
    public function _initialize(){
        // 获取公共 参数
        $commonData     =   getCommonData();

        // 赋值公共参数
        $this->assign('commonData',$commonData);
    }
    public function index()
    {
    	// 获取setting
    	$setting 			=	getSetting();
    	// 获取展示多少数据
    	$indexListShowNum 	=	$setting['index_list_show_num'];

    	// 获取meta
    	$meta 			=	getMeta();

    	// 获得banner信息
    	$banner 		=	$setting['index_banner'];
    	// 处理banner信息
    	$bannerData 	=	explode('{fenge}', $banner);
    	for ($i=0; $i < count($bannerData) ; $i++) { 
    		$bannerData[$i] 	=	explode('{a}', $bannerData[$i]);
    	}

    	// 获取最新商品
    	$newData 		=	Model('BookGoods')->order('good_id desc')->limit($indexListShowNum)->select();
    	// 获取浏览次数最多的商品
    	$hotData 		=	Model('BookGoods')->order('view desc')->limit($indexListShowNum)->select();

    	// 获取楼层id并循环获取楼层信息与商品数据
    	$floorID 		=	$setting['index_floor_id'];
    	// 分割楼层id
    	$floorIDArr 	=	explode(',', $floorID);
    	// 统计楼层数量
    	$floorCount 	=	count($floorIDArr);
    	// 循环获取楼层id的附属id
    	for ($i=0; $i < $floorCount; $i++) { 
    		$next				=	getCateNextAll($floorIDArr[$i]);

    		if($next){
    			$floorIDArr[$i]			=	$next;
    		}else{
    			$floorIDArr[$i] 		=	$floorIDArr[$i];
    		}
    	}
    	// 分别获取数据
    	for ($i=0; $i < $floorCount ; $i++) { 
    		$getData[$i]['data'] 	= Model('BookGoods')
    							->where([
    								'cate' 	=> 	['in',$floorIDArr[$i]]
    							])
    							->select();

    		if($getData[$i]){
    			$getData[$i]['data'] 	=	$getData[$i]['data']->toArray();
    		}

    		// 获取cate标题
    		if(is_array($floorIDArr[$i])){
    			$titleArr 					=	Model('BookCates')->get($floorIDArr[$i][0]);
    		}else{
    			$titleArr 					=	Model('BookCates')->get($floorIDArr[$i]);
    		}
    		
            // 增加参数
    		if($titleArr){
    			$titleArr 	=	$titleArr->toArray();
    			$title 		=	$titleArr['cate_name'];
    			$getData[$i]['title'] 		=	$title;
    			$getData[$i]['id'] 			=	$titleArr['cate_id'];
    		}

    		
    	}

    	$this->assign('meta',$meta);
    	$this->assign('banner',$bannerData);
    	$this->assign('newData',$newData);
    	$this->assign('hotData',$hotData);
    	$this->assign('getData',$getData);

    	return view();
    }
    /*
		*	提示信息
    */
	public function cavaet($msg){
		// 获取meta
		$meta 			=	getMeta('cavaet','',3,$msg);


		$this->assign('msg',$msg);
		$this->assign('meta',$meta);

		return view();
	}
	
   
}
