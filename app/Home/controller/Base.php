<?php

namespace app\home\controller;

use app\BaseController;
use think\facade\Session;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cookie;
use think\facade\Cache;

class Base extends BaseController
{
    public $class; //网站常量
    public $stu; //登入学生

    // 初始化：未登录的用户不允许进入
    protected function initialize()
    {
        $this->stu = Session::get('stu');

        $stu = Session::get('stu');
        if (!$stu) {
            if (Request::isAjax()) {
                exit(json_encode(['code' => 1, 'msg' => '你还未登录，请登录']));
            }
            exit('你还未登录，请登录
            <script>
                setTimeout(function(){window.parent.location.href="/www/login"},2000)
            </script>
            ');
        }
        $this->class = Db::table('classes')->where('class_id', $this->stu['class_id'])->find();
        // 2.取出cookie中的randstr，做登录校验，不允许多人登录同一账号
        $randstr_www = Cookie::get('randstr_www');
        $cache_randstr = Cache::get('www_' . $stu['class_id'] . $stu['stu_no']);
        if ($randstr_www != $cache_randstr) {
            Session::delete('stu');
            if (Request::isAjax()) {
                exit(json_encode(['code' => 1, 'msg' => '你的账号在其他地方登录，请重新登录']));
            }
            exit('你的账号在其他地方登录，请重新登录<script>setTimeout(function(){window.parent.location.href="/www/login"},2000)</script>');
        }
    }

    // 退出登录
    public function exit()
    {
        Session::delete('stu');
    }
}
