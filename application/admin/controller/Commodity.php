<?php
namespace app\admin\controller;

use think\Session;
use think\Controller;
use think\Cookie;
use think\Model;

/*
	*	商品
*/

class Commodity extends Controller{
	// 自动加载 如果没有session : admin_id 跳转到登录页面
	public function _initialize(){
		if(!Session::has('admin_id')){
			$this->redirect(url('admin/login/index'));
		}else{
			$adminSession 	=	Session('admin_id');
		}
	}
	public function index(){
		// 跳转到全部商品
		$this->redirect(url('admin/commodity/all_commodity'));
	}
	/*
		*	全部商品
		*	根据switch 来获取
		*	status 	= 	'' 	全部商品
		*	status 	= 	0 	仓库中的商品
		*	status 	= 	1	出售中的商品
	*/
	public function all_commodity($status=''){
		// 获取系统设置
		$setting 		=	getSetting();

		// 获取后台每页显示数量
		$adminShowNum	=	$setting['admin_list_show_num'];

		// 获取当前页数 没有为1 
		if(!input('page')){
			$page = 1;
		}else{
			$page = input('page');
		}

		switch ($status) {
			// 全部商品
			case '': 
				// 获取所有商品根据ID 倒序
				$allGoods  		=	Model('BookGoods')
										->order('good_id desc')
										->limit($adminShowNum)
										->page($page)
										->select();

				// 分页显示
				$pageination 	=	Model('BookGoods')->paginate($adminShowNum);
			break;
			
			// 仓库中的商品
			case '0':
				// 获取所有商品根据ID 倒序
				$allGoods  		=	Model('BookGoods')
										->where('status',$status)
										->order('good_id desc')
										->limit($adminShowNum)
										->page($page)
										->select();

				// 分页显示
				$pageination 	=	Model('BookGoods')->where('status',$status)->paginate($adminShowNum);
			break;

			// 出售中的商品
			case '1':
				// 获取所有商品根据ID 倒序
				$allGoods  		=	Model('BookGoods')
										->where('status',$status)
										->order('good_id desc')
										->limit($adminShowNum)
										->page($page)
										->select();

				// 分页显示
				$pageination 	=	Model('BookGoods')->where('status',$status)->paginate($adminShowNum);
			break;

			// 超出范围
			default:
				$this->redirect(url('admin/index/cavaet',['msg'=>'超出范围']));
			break;
		}
		

		// 赋值所有商品
		$this->assign('allGoods',$allGoods);

		// 赋值分页
		$this->assign('pageination',$pageination);

		// 赋值status 
		$this->assign('status',$status);

		return view();
	}
	/*
		*	发布商品视图模块
	*/
	public function create_commodity(){
		// 获取所有分类
		$allCate	=	adminGetCateForOption();

		//	赋值所有分类
		$this->assign('allCate',$allCate);

		return view();
	}
	/*
		*	上传图片
	*/
	public function upload_pic(){
		$file				=	request()->file('myfile');
		if(empty($file)){
			return $this->error('缺少必要参数');
		}

		// 获取系统参数
		$setting			=	getSetting();

		// 获取上传路径
		$uploadPath			=	$setting['admin_upload_pic_path'];
		// 获取最大上传字节
		$uploadBigSize		=	$setting['upload_pic_big_size'];
		// 获取可上传的文件后缀
		$uploadExtType		=	$setting['upload_pic_ext_type'];

		// 移动文件
			$move = $file->validate(['size'=>$uploadBigSize,'ext'=>$uploadExtType])->move($uploadPath);
			if($move){
				// 修改path
				$uploadPath 		=	str_replace('../public','', $uploadPath);

				// 增加获得文件路径
				$filePath			=	$move->getSaveName();

				// 修改文件路径
				$filePath			=	str_replace('\\', '/', $filePath);

				// 拼接完整路径
				$fileCompletePath	=	$uploadPath.'/'.$filePath;

				// 返回文件完整路径
				return json([
					'code'	=>	1,
					'msg'	=>	$fileCompletePath,
				]);
			}else{
				return $this->error('上传失败');
			}
	}
	/*
		*	上传图片内容区域
	*/
	public function upload_content(){
		$file				=	request()->file('myfile');

		if(empty($file)){
			return $this->error('参数缺少 上传失败');
		}

		// 获取系统参数
		$setting			=	getSetting();

		// 获取上传路径
		$uploadPath			=	$setting['admin_upload_pic_path'];
		// 获取最大上传字节
		$uploadBigSize		=	$setting['upload_pic_big_size'];
		// 获取可上传的文件后缀
		$uploadExtType		=	$setting['upload_pic_ext_type'];

		// 移动文件
			$move = $file->validate(['size'=>$uploadBigSize,'ext'=>$uploadExtType])->move($uploadPath);
			if($move){
				// 修改path
				$uploadPath 		=	str_replace('../public','', $uploadPath);

				// 增加获得文件路径
				$filePath			=	$move->getSaveName();

				// 修改文件路径
				$filePath			=	str_replace('\\', '/', $filePath);

				// 拼接完整路径
				$fileCompletePath	=	$uploadPath.'/'.$filePath;

				// 返回文件完整路径
				return json([
					'errno'	=>	0,
					'data'	=>	[$fileCompletePath],
				]);
			}else{
				return json(['errno'=>1,'data'=>['']]);
			}
	}
	/*
		*	插入新产品
	*/
	public function new_shop(){
		$data 				=	input('post.');

		// 获取当前时间
		$nowTime 			=	time();

		// 生成图书编码
		$numbering 			=	rand(1,100).$nowTime.rand(1,100).rand(1,100);

		// 添加时间参数
		$data['time']		=	$nowTime;

		// 添加图书编码
		$data['numbering']	=	$numbering;

		// 过滤字符
		if(isset($data['details'])){
			$data['details'] 	=	htmlspecialchars_decode($data['details']);
		}
		

		// 删除good_id
		if(isset($data['good_id'])){
			unset($data['good_id']);
		}

		// 开始添加
		$insert 		=	Model('BookGoods')->insert($data);

		if($insert){
			return $this->success('商品添加成功');
		}else{
			return $this->error('商品添加失败');
		}

	}
	/*
		*	更改商品状态
	*/
	public function status($id){
		if(!isset($id) || empty($id) || !is_numeric($id)){
			return $this->error('参数缺少');
		}

		// 查询商品 并提取status
		$sqlStatusArr   =	Model('BookGoods')->get($id)->toArray();
		$sqlStatus 		=	$sqlStatusArr['status'];

		// 如果状态为 0  则状态为1   如果状态为1   则为0
		if($sqlStatus == 0){
			$nowStatus 	=	1;
		}else{
			$nowStatus 	=	0;
		}

		// 更改
		$updateShop 	=	Model('BookGoods')->where('good_id',$id)->update(['status'=>$nowStatus]);

		if($updateShop){
			return $this->success('状态更新成功');
		}else{
			return $this->error('状态更新失败');
		}
	}
	/*
		*	删除商品
	*/
	public function del($id){
		if(!isset($id) || empty($id) || !is_numeric($id)){
			return $this->error('参数缺少');
		}

		// 开始删除商品 成功返回
		$delStatus 	 =	Model('BookGoods')->where('good_id',$id)->delete();

		if($delStatus){
			return $this->success('删除成功');
		}else{
			return $this->error('删除失败');
		}
	}

	/*
		*	商品搜索
		*	根据status 	
		*	status = null 	搜索全部商品
		*	status = 0		仓库中的商品
		*	status = 1 		出售中的商品
	*/
	public function search_commodity($status='',$title=''){

		// 获取setting
		$setting 			=	getSetting();

		// 获取每页展示多少数量
		$adminShowNum 		=	$setting['admin_list_show_num'];

		if($status == ''){
			$searchArr 		=	Model('BookGoods')
									->where('title','like',"%{$title}%")
									->select();
			// 分页
			$pageination 	=	Model('BookGoods')->where('title','like',"%{$title}%")->paginate($adminShowNum);
		}else{

			$searchArr 		=	Model('BookGoods')
									->where('title','like',"%{$title}%")
									->where('status',$status)
									->select();
			// 分页
			$pageination  	=	Model('BookGoods')->where('title','like',"%{$title}%")
										->where('status',$status)
										->paginate($adminShowNum);
		}

		// 赋值
		$this->assign('title',$title);
		$this->assign('searchArr',$searchArr);
		$this->assign('pageination',$pageination);

		return view();
	}
	/*
		*	编辑商品
	*/
	public function edit_commodity($id){

		// 获取商品参数
		$shopMessage 		=	Model('BookGoods')->get($id);

		if($shopMessage){
			$shopMessage 	=	$shopMessage->toArray();

			// 获取主图 x1 ， 副图 x4 分别分割为数组并分割写入cookie
				// 赋值主图到cookie
				Cookie('pic1',$shopMessage['main_img']);
				// 获取副图
				$fuPic 		=	$shopMessage['vice_img'];
				// 分割幅图为数组
				$fuPicArr 	=	explode(',',$fuPic);

				// 分别赋值副图地址到cookie
				Cookie::set('pic2',$fuPicArr[0]);
				Cookie::set('pic3',$fuPicArr[1]);
				Cookie::set('pic4',$fuPicArr[2]);
				Cookie::set('pic5',$fuPicArr[3]);

			// 获取所有分类
			$allCate	=	adminGetCateForOption();

			// html反转details
			$shopMessage['details'] 	=	htmlspecialchars_decode($shopMessage['details']);

			// 赋值分类信息
			$this->assign('allCate',$allCate);
			// 赋值商品信息
			$this->assign('shopMessage',$shopMessage);
			// 赋值副图信息
			$this->assign('vice_img',$fuPicArr);

			return view();
		}else{
			$this->redirect(url('admin/index/cavaet',['msg'=>'未知商品ID']));
		}
	}
	/*
		*	修改商品
	*/
	public function edit_shop(){
		$data 	=	input('post.');

		// 获取good_id
		$good_id 	=	$data['good_id'];

		// 开始删除所有空值
		foreach ($data as $key => $value) {
			// 格式化字符串
			$data[$key] 	=	htmlspecialchars($data[$key]);

			// 如果为空 删除
			if(empty($data[$key])){
				unset($data[$key]);
			}
		
		}

		// 删除good_id
		if(isset($data['good_id'])){
			unset($data['good_id']);
		}

		// 开始更新
		$update 	=	Model('BookGoods')->where('good_id',$good_id)->update($data);

		if($update){
			return $this->success('修改成功');
		}else{
			return $this->error('修改失败');
		}
	}
}

?>