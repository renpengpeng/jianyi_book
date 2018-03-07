<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------


use think\Route;

Route::rule([
	'/shop/:id'			=>	'index/shop/index',
	'/lists/:id'		=>	'index/lists/index',
	'/page/:id' 		=>	'index/page/index',
	'/login' 			=>	'index/login/index',
	'/reg' 				=>	'index/login/reg',
	'/links' 			=>	'index/page/links',
	'/shop/search' 		=>	'index/search/index'

],'','get',['ext'=>'html'],['id'=>'[\d|\w]+']);

// 登录
// Route::rule('/login','index/login/index','','get',['ext'=>'html'],'');