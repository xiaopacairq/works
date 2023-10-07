<?php

namespace app\home\controller;

use app\BaseController;
use think\captcha\facade\Captcha;
use think\facade\Db;
use think\facade\Session;
use think\facade\Request;
use think\facade\Cookie;
use think\facade\Cache;

class Account extends BaseController
{
    // 登录界面
    public function index()
    {
        return view('/account/login');
    }

    // 注册验证码
    public function verify()
    {
        return Captcha::create();
    }

    // 登录校验
    public function do_login()
    {
        $class_id = (int)Request::post('class_id', '');
        $stu_no =  (int)Request::post('stu_no', '');
        $stu_pwd = Request::post('stu_pwd', '');
        $captcha = Request::post('captcha', '');

        // 非空验证
        if (!$class_id) exit(json_encode(['code' => 1, 'msg' => '班级代码不能为空'], true));
        if (!$stu_no) exit(json_encode(['code' => 1, 'msg' => '学号不能为空'], true));
        if (!$stu_pwd) exit(json_encode(['code' => 1, 'msg' => '密码不能为空'], true));
        if (!$captcha) exit(json_encode(['code' => 1, 'msg' => '验证码不能为空'], true));

        if (!captcha_check($captcha)) exit(json_encode(['code' => 1, 'msg' => '验证码错误'], true));

        //班级是否存在
        $class = Db::table('classes')->where('class_id', $class_id)->find();
        if (!$class) exit(json_encode(['code' => 1, 'msg' => '班级不存在'], true));

        // 用户名是否存在
        $user = Db::table('stu')->where(['class_id' => $class_id, 'stu_no' => $stu_no])->find();
        if (!$user) exit(json_encode(['code' => 1, 'msg' => '学号不存在'], true));

        // 密码校验
        if ($user['stu_pwd'] != $stu_pwd) exit(json_encode(['code' => 1, 'msg' => '密码错误'], true));

        // 通过登录
        $data['last_time'] = date('Y-m-d H:i:s', time());
        $res = Db::table('stu')->where(['class_id' => $class_id, 'stu_no' => $user['stu_no']])->update($data); // 修改登录时间

        Session::set('stu', $user); //将登录信息保存到session中
        Session::delete('captcha'); //删除验证码session

        $randstr_www = time() . rand(10, 99); // 存用户id-userkey（随机字符）
        Cache::set('www_' .  $user['class_id'] . $user['stu_no'], $randstr_www);  //保存到服务器端
        Cookie::Set('randstr_www', $randstr_www);  //保存到cookie前端

        // exit会导致session失效
        echo json_encode(['code' => 0, 'msg' => '登录成功'], true);
    }
}