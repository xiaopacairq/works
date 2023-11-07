<?php

namespace app\admin\controller;

use think\facade\Db;
use think\facade\Request;
use think\facade\View;
use file\File;

/**
 * 后台页面类
 * index 班级主页页面
 * save 班级信息保存
 */
class Classed extends Base
{
    public function index()
    {
        $data['admin'] = $this->admin;
        $class_id = (int)Request::get('class_id', '');
        if (!$class_id) {
            exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
        }
        $data['class'] = Db::table('classes')->where('class_id', $class_id)->find();
        $data['class']['title'] = '班级配置';  //动态设置网站名称

        //使用模板
        View::engine()->layout('layout');
        return View::fetch('classed/index', $data);
    }

    public function save()
    {
        $class_id = trim(Request::post('class_id', ''));
        $data['class_name'] = trim(Request::post('class_name', ''));
        $data['class_time'] = trim(Request::post('class_time', ''));
        $data['remarks'] = trim(Request::post('remarks', ''));

        // 非空验证
        if ($class_id  == '') {
            exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
        }
        if ($data['class_name']  == '') {
            exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
        }

        //表单长度填写限制
        if (mb_strlen($data['class_name']) > 30 || mb_strlen($data['class_time']) > 30) {
            exit(json_encode(['code' => 1, 'msg' => '表单填写长度超出限制！'], true));
        }
        if (mb_strlen($data['remarks']) > 150) {
            exit(json_encode(['code' => 1, 'msg' => '备注填写长度超出限制！'], true));
        }

        // 修改数据
        Db::table('classes')->where('class_id', $class_id)->update($data);
        exit(json_encode(['code' => 0, 'msg' => '保存成功'], true));
    }
}
