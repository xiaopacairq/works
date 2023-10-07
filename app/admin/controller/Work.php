<?php

namespace app\admin\controller;

use think\facade\Config;
use think\facade\Db;
use think\facade\request;
use think\facade\Session;
use think\facade\View;
use file\File;
use file\Zip1;

/**
 * 后台页面类
 * 作业权限开放方法
 * index 作业开放主页
 * add 作业布置
 * edit 作业信息修改
 */
class Work extends Base
{
    // 主页
    public function index()
    {
        $class_id = (int)Request::get('class_id', '');
        if (!$class_id) {
            exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
        }
        $data['class'] = Db::table('classes')->where('class_id', $class_id)->find();
        $data['admin'] = $this->admin;
        $data['recent_date'] = date("Y-m-d H:i", time());
        $data['class']['title'] = '作业布置';

        $data['work'] = Db::table('work')->where('class_id', $class_id)->order('work_id', 'desc')->select();
        View::engine()->layout('layout');
        return View::fetch('work/index', $data);
    }

    // 添加
    public function add()
    {
        if (Request::isPost()) {
            $class_id = (int)Request::post('class_id', '');
            $data['class_id'] = (int)Request::post('class_id', '');
            $data['work_id'] = (int)Request::post('work_id', '');
            $data['work_remarks'] = trim(Request::post('work_remarks', ''));
            $data['work_last_time'] = Request::post('work_last_time', '');
            $data['status'] = (int)Request::post('status', '0');

            if ($data['work_id'] == '' || $data['work_id'] <= 0) {
                exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
            }
            if ($data['work_remarks'] == '') {
                exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
            }
            if (mb_strlen($data['work_remarks']) > 150) {
                exit(json_encode(['code' => 1, 'msg' => '备注填写长度超出限制！'], true));
            }
            if ($data['work_last_time'] == '') {
                exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
            }

            $res = Db::table('work')->where(['class_id' => $class_id, 'work_id' => $data['work_id']])->find();


            if ($res) {
                exit(json_encode(['code' => 1, 'msg' => '作业号已存在'], true));
            }

            $data['work_start_time'] = date('Y-m-d H:i:s', time());
            $res = Db::table('work')->insert($data);

            if ($res) {
                exit(json_encode(['code' => 0, 'msg' => '保存成功'], true));
            }
            exit(json_encode(['code' => 1, 'msg' => '保存失败'], true));
        } else {
            $data['class_id'] = (int)Request::get('class_id', '');

            // dump($data);

            return View::fetch('work/add', $data);
        }
    }

    // 修改
    public function edit()
    {
        if (Request::isPost()) {
            $id = (int)Request::post('id', '');
            $class_id = (int)Request::post('class_id', '');

            if (empty($id)) { //如果没有get参数，则返回
                exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
            }
            if (empty($class_id)) { //如果没有get参数，则返回
                exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
            }

            // $data['work_id'] = (int)Request::post('work_id', '');  //不修改作业名称
            $data['work_remarks'] = trim(Request::post('work_remarks', ''));
            $data['work_last_time'] = Request::post('work_last_time', '');
            $data['status'] = (int)Request::post('status', '0');

            if ($data['work_remarks'] == '') {
                exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
            }
            if (mb_strlen($data['work_remarks']) > 150) {
                exit(json_encode(['code' => 1, 'msg' => '备注填写长度超出限制！'], true));
            }
            if ($data['work_last_time'] == '') {
                exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
            }

            Db::table('work')->where(['class_id' => $class_id, 'id' => $id])->update($data);
            exit(json_encode(['code' => 0, 'msg' => '保存成功'], true));
        } else {
            $id = (int)Request::get('id', '');
            $class_id = (int)Request::get('class_id', '');

            if (empty($id)) { //如果没有get参数，则返回
                exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
            }
            if (empty($class_id)) { //如果没有get参数，则返回
                exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
            }

            $data['work_id'] = (int)Request::get('work_id', '');
            if (empty($id)) {
                exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
            }

            $data['work'] = Db::table('work')->where(['id' => $id])->find();  //根据唯一id查到作业

            $data['works'] = Db::table('stu')->where('class_id', $class_id)->select()->toArray();
            foreach ($data['works'] as $k => $v) {
                if (Db::table('is_work')->where(['stu_no' => $v['stu_no'], 'work_id' => $data['work_id']])->find()) { //如果学生已点击
                    if (Db::table('is_work')->where(['stu_no' => $v['stu_no'], 'work_id' => $data['work_id'], 'is_true' => 1])->find()) { //如果学生点击作业且确认上传
                        $data['works'][$k]['is_true'] = 1;
                        $data['works'][$k]['work_id'] = $data['work_id'];
                        $data['works'][$k]['save_time'] = Db::table('is_work')->where(['stu_no' => $v['stu_no'], 'work_id' => $data['work_id'], 'is_true' => 1])->find()['last_time']; //提交作业时间
                        $score_sum = Db::table('score')->where(['stu_no' => $v['stu_no'], 'work_id' => $data['work_id']])->sum('score');  //获取的总分
                        $stu_count = Db::table('stu')->count(); //学生总人数

                        $data['works'][$k]['all_score'] = number_format($score_sum / $stu_count, 2);
                    } else { //学生点击作业，但未上传
                        $data['works'][$k]['is_true'] = 0;
                        $data['works'][$k]['work_id'] = $data['work_id'];
                        $data['works'][$k]['save_time'] = 0; //提交作业时间
                        $data['works'][$k]['all_score'] = 0; //分数为0
                    }
                } else { //如果学生未点击作业
                    $data['works'][$k]['is_true'] = -1;
                    $data['works'][$k]['work_id'] = $data['work_id'];
                    $data['works'][$k]['save_time'] = 0; //提交作业时间
                    $data['works'][$k]['all_score'] = 0; //分数为0
                }
            }

            return View::fetch('work/edit', $data);
        }
    }

    // 删除
    public function del()
    {
        if (Request::isPost()) {
            $id = (int)Request::post('id', '');  //删除单条作业
            $class_id = (int)Request::post('class_id', '');
            $work_id = Request::post('work_id', '');  //删除作业的score、is_work、works记录

            $file = new file();
            if (file_exists("storage" . "/" . $class_id . '/stu_work' . '/' . $work_id)) { //判断文件是否存在
                //删除单条数据
                $file->remove_dir("storage" . "/" . $class_id . '/stu_work' . '/' . $work_id, true); //清空作业目录
            }

            Db::table('work')->where('id', $id)->delete();
            Db::table('is_work')->where(['class_id' => $class_id, 'work_id' => $work_id])->delete();
            Db::table('works')->where(['class_id' => $class_id, 'work_id' => $work_id])->delete();
            Db::table('score')->where(['class_id' => $class_id, 'work_id' => $work_id])->delete();

            // 清空文件--------------
            exit(json_encode(['code' => 0, 'msg' => '删除成功'], true));
        } else {
            exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
        }
    }

    public function detail()
    {
        $class_id = Request::get('class_id', '');
        if (empty($class_id)) { //如果没有get参数，则返回
            exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
        }
        $data['stu_no'] = (int)Request::get('stu_no', '');
        $data['work_id'] = (int)Request::get('work_id', '');
        $data['score'] = Db::table('score')->where(['class_id' => $class_id, 'work_id' => $data['work_id'], 'stu_no' => $data['stu_no']])->select();

        return View::fetch('work/detail', $data);
    }

    // 作业导出
    public function get_zip()
    {
        $class_id = (int)Request::get('class_id', '');

        $zip1 = new Zip1();
        $zip1->zip($class_id, 'stu_work');
    }
}