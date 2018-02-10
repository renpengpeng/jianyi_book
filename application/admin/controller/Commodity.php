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
		}
	}
	public function index(){
		// 跳转到全部商品
		$this->redirect(url('admin/commodity/all_commodity'));
	}
	/*
		*	全部商品
	*/
	public function all_commodity(){
		return view();
	}
	/*
		*	发布商品
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
			return json(['code'=>0,'msg'=>'参数缺少']);
		}

		// 获取系统参数
		$setting			=	getSetting();

		// 获取上传路径
		$uploadPath			=	$setting['admin_upload_pic_path'];
		// 获取上传加密方式
		$uploadEncryption	=	$setting['upload_pic_encryption_type'];
		// 获取最大上传字节
		$uploadBigSize		=	$setting['upload_pic_big_size'];
		// 获取可上传的文件后缀
		$uploadExtType		=	$setting['upload_pic_ext_type'];

		// 移动文件
		if($uploadEncryption == 0){
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
				return json(['code'=>0,'上传失败']);
			}
		}
	}
}

?>