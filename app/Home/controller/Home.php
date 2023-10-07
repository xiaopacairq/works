<?php

namespace app\home\controller;

use think\captcha\facade\Captcha;
use think\facade\Db;
use think\facade\Session;
use think\facade\Request;
use think\facade\View;


/**
 * 主页面
 */
class Home extends Base
{
    public function index()
    {
        $data['class'] = $this->class;
        $data['stu'] = $this->stu;
        $data['class']['title'] = '学生主页';

        View::engine()->layout('layout');
        return View::fetch('home/index',  $data);
    }
}