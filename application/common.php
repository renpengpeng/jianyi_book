<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/*
	> 发送邮件，用于注册验证
	*	@varchar	$
*/
function sendMail($sendName,$sendMail,$sendTitle,$sendContent,$addUser){
	// 如果都没有设置则不往下执行
	if(!$sendName || !$sendMail || !$sendTitle || !$sendContent || !$addUser){
		echo json(['code'=>0,'msg'=>'参数不完整']);
		exit;
	}

	try {
		// 初始化phpmailer
		$mail 	=	new \mail\PHPMailer();

		$mail->SMTPDebug = 0;
	 
		//使用smtp鉴权方式发送邮件，当然你可以选择pop方式 sendmail方式等 本文不做详解
		//可以参考http://phpmailer.github.io/PHPMailer/当中的详细介绍
		$mail->isSMTP();
		//smtp需要鉴权 这个必须是true
		$mail->SMTPAuth=true;
		//链接qq域名邮箱的服务器地址
		$mail->Host = 'smtp.qq.com';
		//设置使用ssl加密方式登录鉴权
		$mail->SMTPSecure = 'ssl';
		//设置ssl连接smtp服务器的远程服务器端口号 可选465或587
		$mail->Port = 465;
		//设置smtp的helo消息头 这个可有可无 内容任意
		$mail->Helo = 'Hello smtp.qq.com Server';
		//设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
		$mail->Hostname = 'renpengpeng.com';
		//设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
		$mail->CharSet = 'UTF-8';
		//设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
		$mail->FromName = $sendName;
		//smtp登录的账号 这里填入字符串格式的qq号即可
		$mail->Username ='2280120391';
		//smtp登录的密码 这里填入“独立密码” 若为设置“独立密码”则填入登录qq的密码 建议设置“独立密码”
		$mail->Password = 'sthiecoelwlrebig';
		//设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
		$mail->From = $sendMail;
		//邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
		$mail->isHTML(true); 
		//设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
		$mail->addAddress($addUser);
		//添加多个收件人 则多次调用方法即可
		// $mail->addAddress('xxx@163.com','晶晶在线用户');
		//添加该邮件的主题
		$mail->Subject = $sendTitle;
		//添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
		$mail->Body = $sendContent;

		if(!$mail->send()){
			throw new Exception();
		}else{
			return json(['code'=>0,'msg'=>'发送成功']);
		}
	} catch (Exception $e) {
		echo $e->getMessage();
	}
	
}
/*
	*	获取所有的分类 array显示
*/
function adminGetCateForArray($pid=0){

	// 开始获取
	$result 	=	Model('BookCates')->where('pid',$pid)->select()->toArray();

	foreach ($result as $key => $value) {
		// 查询分类下的数量
		$cateNum	=	Model('BookCates')->where('pid',$result[$key]['cate_id'])->count();

		if($cateNum > 0){
			$result[$key] 	+=	Model('BookCates')->where('pid',$result[$key]['cate_id'])->select()->toArray();
		}

	}

	$pid++;
	// 再次统计
	$tj	=	Model('BookCates')->where('pid',$pid)->count();

	if($tj > 0){
		adminGetCateForArray($tj);
	}

	return $result;
}
/*
	*	获取所有分类li显示
*/
function adminGetCateForLi($pid=0){

	$result = '<ul>';

	// 开始获取
	$oneGet	=	Model('BookCates')->where('pid',$pid)->order('cate_id asc')->select()->toArray();

	foreach ($oneGet as $key => $value) {
		$result .= '<li class="list-group-item">';
		// 设置result 
		$result .=	str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $oneGet[$key]['garden']).$oneGet[$key]['cate_name'];
		$result	.=	'<span class="btn btn-sm btn-danger" style="float:right;margin-left:10px;">';
		$result	.=	'<a href="'.url('admin/classification/del',['id'=>$oneGet[$key]['cate_id']]).'" onclick="return hrefMsg($(this))">删除</a>';
		$result	.=	'</span>';
		$result	.=	'<span class="btn btn-sm btn-success" style="float:right;">';
		$result	.=	'<a href="'.url('admin/classification/edit',['id'=>$oneGet[$key]['cate_id']]).'">编辑</a>';
		$result	.=	'</span>';

		// 如果查询分类数量
		$ziNum	=	Model('BookCates')->where('pid',$oneGet[$key]['cate_id'])->count();

		if($ziNum > 0){
			// 查询ID
			$geID	=	Model('BookCates')->where('pid',$oneGet[$key]['cate_id'])->order('cate_id asc')->select()->toArray();

			// 遍历
			foreach ($geID as $k => $v) {
				$result	.=	'<li class="list-group-item">';
				$result .=	str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $geID[$k]['garden']).$geID[$k]['cate_name'];
				$result	.=	'<span class="btn btn-sm btn-danger" style="float:right;margin-left:10px;">';
				$result	.=	'<a href="'.url('admin/classification/del',['id'=>$geID[$k]['cate_id']]).'" onclick="return hrefMsg($(this))">删除</a>';
				$result	.=	'</span>';
				$result	.=	'<span class="btn btn-sm btn-success" style="float:right;">';
				$result	.=	'<a href="'.url('admin/classification/edit',['id'=>$geID[$k]['cate_id']]).'">编辑</a>';
				$result	.=	'</span>';
				$result	.=	'</li>';
				$result .=	adminGetCateForLi($geID[$k]['cate_id']);
			}
		}
		$result .= '</li>';
	}
	$result .= '</ul>';

	return $result;
}
/*
	*	option
*/
function adminGetCateForOption($pid=0){

	$result = '';

	// 开始获取
	$oneGet	=	Model('BookCates')->where('pid',$pid)->order('cate_id asc')->select()->toArray();

	foreach ($oneGet as $key => $value) {
		$result .= '<option value="'.$oneGet[$key]['cate_id'].'">';
		// 设置result 
		$result .= str_repeat('|——', $oneGet[$key]['garden']).'&nbsp;&nbsp;'.$oneGet[$key]['cate_name'];


		// 如果查询分类数量
		$ziNum	=	Model('BookCates')->where('pid',$oneGet[$key]['cate_id'])->count();

		if($ziNum > 0){
			// 查询ID
			$geID	=	Model('BookCates')->where('pid',$oneGet[$key]['cate_id'])->order('cate_id asc')->select()->toArray();

			// 遍历
			foreach ($geID as $k => $v) {
				$result .= '<option value="'.$geID[$k]['cate_id'].'">';
				$result .=	str_repeat('|——', $geID[$k]['garden']).'&nbsp;&nbsp;'.$geID[$k]['cate_name'];
				$result	.=	'</option>';
				$result .=	adminGetCateForOption($geID[$k]['cate_id']);
			}
		}
		$result .= '</option>';
	}

	return $result;
}

/*
	*	遍历某个分类下的所有分类个数
*/
function getCateNextAll($id){

	$result		=	[$id];

	// 获取 pid为$id 的所有分类
	$oneNext	=	Model('BookCates')->where('pid',$id)->order('cate_id asc')->select()->toArray();

	if(!$oneNext){
		return false;
	}

	// 遍历 把id放到$result 里面
	foreach ($oneNext as $key => $value) {
		$ids		=	$oneNext[$key]['cate_id'];
		$result[]	=	$ids;

		// 查询是否有pid为此分类的分类
		$count		=	Model('BookCates')->where('pid',$ids)->order('cate_id asc')->count();
		if($count >= 1){
			$result += array_merge($result,getCateNextAll($ids));
		}	
	}

	return array_values(array_unique($result));
}

/*
	*	获取系统参数
	*	getSetting()
*/
function getSetting(){
	// 调用模型
	$result = Model('BookSetting')->get(1)->toArray();

	return $result;
}