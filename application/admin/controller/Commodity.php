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

		// 获取所有商品根据ID 倒序
		$allGoods  		=	Model('BookGoods')
								->order('good_id desc')
								->limit($adminShowNum)
								->page($page)
								->select();

		// 分页显示
		$pageination 	=	Model('BookGoods')->paginate($adminShowNum);

		// 赋值所有商品
		$this->assign('allGoods',$allGoods);

		// 赋值分页
		$this->assign('pageination',$pageination);

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

		// 过滤商品描述的字符
		$data['details']	=	htmlspecialchars($data['details']);

		// 开始添加
		$insert 		=	Model('BookGoods')->insert($data);

		if($insert){
			return json(['code'=>1,'msg'=>'添加成功']);
		}else{
			return json(['code'=>0,'msg'=>'添加失败']);
		}

	}
	/*
		*	更改商品状态
	*/
	public function status($id){
		if(!isset($id) || empty($id) || !is_numeric($id)){
			return json(['code'=>0,'msg'=>'参数缺少']);
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
			return json(['code'=>1,'msg'=>'状态更改成功']);
		}else{
			return json(['code'=>0,'msg'=>'状态更改失败']);
		}
	}
	/*
		*	删除商品
	*/
	public function del($id){
		if(!isset($id) || empty($id) || !is_numeric($id)){
			return json(['code'=>0,'msg'=>'参数缺少']);
		}

		// 开始删除商品 成功返回
		$delStatus 	 =	Model('BookGoods')->where('good_id',$id)->delete();

		if($delStatus){
			return json(['code'=>1,'msg'=>'删除成功']);
		}else{
			return json(['code'=>0,'msg'=>'删除失败']);
		}
	}
	/*
		*	出售中的商品
	*/
	public function sale_commodity(){
		// 获取系统setting
		$setting 		= 	getSetting();

		// 获取每页显示多少数量
		$adminShowNum 	=	$setting['admin_list_show_num'];

		// 判断page
		if(!input('page')){
			$page = 1;
		}else{
			$page = input('page');
		}

		// 根据page获取正在出售中的商品 good_id倒序   status为1
		$saleArr 	=	Model('BookGoods')
							->where('status',1)
							->order('good_id desc')
							->limit($adminShowNum)
							->page($page)
							->select();

		// 分页
		$pageination 	=	Model('BookGoods')->where('status',1)->paginate($adminShowNum);

		// 赋值商品 
		$this->assign('saleArr',$saleArr);

		// 赋值分页
		$this->assign('pageination',$pageination);

		return view();
	}
}

?>