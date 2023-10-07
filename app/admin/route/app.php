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
use think\facade\Route;

Route::get('login', 'account/index');   //重定向到login 注意：/account写法是错的

Route::get('welcome', 'home/index');   //重定向到欢迎页面--班级管理页面

Route::get('class_wz', 'classed/index');   //重定向到班级设置页面

Route::get('student', 'stu/index');   //重定向到学生管理页面

Route::get('works', 'work/index');   //重定向到作业布置页面

Route::get('scores', 'score/index');   //重定向到成绩管理页面