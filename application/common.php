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