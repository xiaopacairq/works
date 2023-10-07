<?php

namespace app\admin\controller; //定义多应用下命名空间

use app\BaseController;
use think\captcha\facade\Captcha;
use think\facade\Db;
use think\facade\Session;
use think\facade\Request;
use think\facade\Cookie;
use think\facade\Cache;

/**
 * 后台管理系统登录校验类
 * index:     生成登录页面方法
 * verify：   生成验证码方法
 * do_login:  登录校验方法
 * 
 */
class Account extends BaseController
{
    // 登录界面
    public function index()
    {
        return view('/account/login'); //加载视图页面
    }

    // 注册验证码
    public function verify()
    {
        return Captcha::create();  //创建验证码
    }

    // 登录校验
    public function do_login()
    {
        // 获取登录信息
        $uname = trim(Request::post('uname', ''));
        $pwd = Request::post('pwd', '');
        $captcha = trim(Request::post('captcha', ''));

        // 非空校验
        if (!$uname) exit(json_encode(['code' => 1, 'msg' => '账户不能为空'], true));
        if (!$pwd) exit(json_encode(['code' => 1, 'msg' => '密码不能为空'], true));
        if (!$captcha) exit(json_encode(['code' => 1, 'msg' => '验证码不能为空'], true));
        if (!captcha_check($captcha)) exit(json_encode(['code' => 1, 'msg' => '验证码错误'], true)); // 验证码校验
        $user = Db::table('admin')->where('uname', $uname)->find();  // 用户名是否存在校验
        if (!$user) exit(json_encode(['code' => 1, 'msg' => '账户不存在'], true));
        if ($user['pwd'] != $pwd) exit(json_encode(['code' => 1, 'msg' => '密码错误'], true)); // 密码校验

        // 通过登录
        $data['last_time'] = date('Y-m-d H:i:s', time());  //记录登录时间
        Db::table('admin')->where('uname', $user['uname'])->update($data); // 修改登录时间

        // 分别保存用户信息到Session【服务端记录用户信息】、cookie、cache【浏览器端记录用户信息】
        Session::set('admin', $user); //将登录信息保存到session中
        Session::delete('captcha'); //删除验证码session
        $randstr_houtai = time() . rand(10, 99); // 存用户id-userkey（随机字符）
        Cache::set('houtai_' . $user['id'], $randstr_houtai);  //保存到服务器端
        Cookie::Set('randstr_houtai', $randstr_houtai);  //保存到cookie前端

        echo json_encode(['code' => 0, 'msg' => '登录成功'], true); // exit会导致session失效
    }
}