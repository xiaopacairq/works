<?php

namespace app\admin\controller;

use app\BaseController;
use think\facade\Session;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cookie;
use think\facade\Cache;

class Base extends BaseController
{
    public $admin; //登入用户

    // 初始化：未登录的用户不允许进入
    protected function initialize()
    {
        $this->admin = Session::get('admin');

        $admin = Session::get('admin');
        if (!$admin) {
            if (Request::isAjax()) {
                exit(json_encode(['code' => 1, 'msg' => '你还未登录，请登录']));
            }
            exit('你还未登录，请登录
            <script>
                setTimeout(function(){window.parent.location.href="/houtai/login"},2000)
            </script>
            ');
        }

        // 2.取出cookie中的randstr，做登录校验，不允许多人登录同一账号
        $randstr_houtai = Cookie::get('randstr_houtai');
        $cache_randstr = Cache::get('houtai_' . $admin['id']);
        if ($randstr_houtai != $cache_randstr) {
            Session::delete('admin');
            if (Request::isAjax()) {
                exit(json_encode(['code' => 1, 'msg' => '你的账号在其他地方登录，请重新登录']));
            }
            exit('你的账号在其他地方登录，请重新登录<script>setTimeout(function(){window.parent.location.href="/houtai/login"},2000)</script>');
        }
    }

    // 退出登录
    public function exit()
    {
        Session::delete('admin');
    }
}