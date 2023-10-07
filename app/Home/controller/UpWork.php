<?php

namespace app\home\controller;

use think\facade\Db;
use think\facade\Session;
use think\facade\Request;
use think\facade\View;
use think\facade\Filesystem;
use file\File;


/**
 * 作业上传
 * 
 * 
 */
class UpWork extends Base
{
    public function index()
    {
        $data['class'] = $this->class;
        $data['stu'] = $this->stu;
        $data['recent_date'] = date("Y-m-d H:i", time());
        $data['class']['title'] = '作业上传';
        $data['work'] = Db::table('work')->where(['class_id' => $data['class']['class_id'], 'status' => 0])->order('work_id', 'desc')->select()->toArray();



        foreach ($data['work'] as $k => $v) {
            if (Db::table('is_work')
                ->where(['class_id' => $data['class']['class_id'], 'stu_no' => $data['stu']['stu_no'], 'work_id' => $v['work_id']])
                ->find()
            ) { //可以查到对应的数据，学生已确认
                if (Db::table('is_work')
                    ->where(['class_id' => $data['class']['class_id'], 'stu_no' => $data['stu']['stu_no'], 'work_id' => $v['work_id'], 'is_true' => 1])
                    ->find()
                ) { //学生已提交
                    $data['work'][$k]['stu_no'] = $data['stu']['stu_no'];
                    $data['work'][$k]['is_true'] = 1;
                    $score_sum = Db::table('score')
                        ->where(['class_id' => $data['class']['class_id'], 'stu_no' => $data['stu']['stu_no'], 'work_id' => $v['work_id']])
                        ->sum('score');  //获取的总分
                    $stu_count = Db::table('stu')->where(['class_id' => $data['class']['class_id']])->count(); //学生总人数

                    $data['work'][$k]['all_score'] = number_format($score_sum / $stu_count, 2);
                } else {
                    // 学生未提交
                    $data['work'][$k]['stu_no'] = $data['stu']['stu_no'];
                    $data['work'][$k]['is_true'] = 0;
                }
            } else { //不能查到对应的数据，学生未确认
                $data['work'][$k]['stu_no'] = $data['stu']['stu_no'];
                $data['work'][$k]['is_true'] = -1;
            }
        }

        View::engine()->layout('layout');
        return View::fetch('up_work/index', $data);
    }

    // 主页面
    public function details()
    {
        $class = $this->class;

        $data['stu'] = $this->stu;
        $data['class_id'] = $class['class_id'];
        $data['stu_no'] = (int)Request::get('stu_no', '');  //当前作业号
        $data['work_id'] = (int)Request::get('work_id', '');  //当前学号
        $data['recent_date'] = date("Y-m-d H:i", time());  //当前的时间，与作业时间对比
        $data['work'] = Db::table('work')->where(['class_id' => $class['class_id'], 'work_id' => $data['work_id']])->find();  //获取当前作业的信息,包括截止时间和作业的状态，确保作业在截止时间结束后或状态关闭时，是无法上传作业的！

        if ($data['work_id'] == "" || $data['stu_no']  == "") { //不允许非法进入
            exit(json_encode(['code' => 1, "msg" => "非法请求"]));
        }

        $res = Db::table('is_work')->where(['class_id' => $class['class_id'], 'stu_no' => $data['stu_no'], 'work_id' => $data['work_id']])->find();
        if (!$res) {
            //如果没有查询到对应的is_work的数据，则添加一条数据
            Db::table('is_work')
                ->insert([
                    'class_id' => $class['class_id'],
                    'work_id' => $data['work_id'],
                    'stu_no' => $data['stu_no'],
                    'is_true' => '0',
                    'last_time' => date("Y-m-d H:i", time())
                ]);
        }

        $data['works'] = Db::table('works')  //获取已上传的文件信息
            ->where(['class_id' => $class['class_id'], 'stu_no' => $data['stu_no'], 'work_id' => $data['work_id']])
            ->select();

        $data['is_work'] = Db::table('is_work')->where(['class_id' => $class['class_id'], 'stu_no' => $data['stu_no'], 'work_id' => $data['work_id']])->find();

        return View::fetch('up_work/details', $data);
    }

    //确认上传作业
    public function is_true()
    {
        $class = $this->class;

        $data['class_id'] = $class['class_id'];
        $data['stu_no'] = (int)Request::post('stu_no', '');
        $data['work_id'] = (int)Request::post('work_id', '');
        $data['is_true'] = (int)Request::post('is_true', '0');
        $data['last_time'] = date("Y-m-d H:i:s", time());
        $res = Db::table('work')->where(['class_id' => $class['class_id'], 'work_id' => $data['work_id']])->find();
        if ($res['status'] == 1) {
            exit(json_encode(['code' => 1, 'msg' => "作业已关闭"]));
        }

        // 如果确认提交，要确保提交文件中至少有一个index文件
        if ($data['is_true'] == 1) {
            $res = Db::table('works')
                ->where(
                    ['class_id' => $class['class_id'], 'stu_no' => $data['stu_no'], 'work_id' => $data['work_id']],
                )
                ->where('filename', 'like', 'index.' . '%')
                ->find();
            if (!$res) {
                exit(json_encode(['code' => 1, 'msg' => "请上传index文件"]));
            }
        }

        if (Db::table('is_work')->where(['class_id' => $class['class_id'], 'stu_no' => $data['stu_no']])->find() == "" || Db::table('is_work')->where(['class_id' => $class['class_id'], 'work_id' => $data['work_id']])->find() == "") {
            // 第一次上传，添加一条数据
            Db::table('is_work')->insert($data);
            exit(json_encode(['code' => 0, 'msg' => "保存成功"]));
        } else {
            // 执行修改操作
            Db::table('is_work')->where(['class_id' => $class['class_id'], 'stu_no' => $data['stu_no'], 'work_id' => $data['work_id']])->update($data);
            $data['is_true'] == 0 ? exit(json_encode(['code' => 0, 'msg' => "取消成功"])) : exit(json_encode(['code' => 0, 'msg' => "保存成功"]));
        }
    }

    // 上传文件
    public function upfile()
    {
        $class = $this->class;

        $file = Request::file('file');
        $data['class_id'] = $class['class_id'];
        $data['stu_no'] = Request::post('stu_no', '');
        $data['work_id'] = Request::post('work_id', '');
        $data['filename'] = Request::file('file')->getOriginalName();
        $res = Db::table('work')->where(['class_id' => $class['class_id'], 'work_id' => $data['work_id']])->find();
        if ($res['status'] == 1) {
            exit(json_encode(['code' => 1, 'msg' => "作业已关闭"]));
        }
        $count = Db::table('works')->where(['class_id' => $class['class_id'], 'work_id' => $data['work_id'], 'stu_no' => $data['stu_no']])->count();  //当前文件个数
        if ($count > 15) {
            exit(json_encode(['code' => 1, 'msg' => "最多上传15个文件"]));
        }

        if ($data['stu_no'] == "") {
            exit(json_encode(['code' => 1, 'msg' => "非法请求"]));
        }
        if ($data['work_id'] == "") {
            exit(json_encode(['code' => 1, 'msg' => "非法请求"]));
        }

        $data['work_url'] = "storage" . "/" . $class['class_id'] . "/stu_work" . "/" . $data['work_id'] . "/" . $data['stu_no'] . "/" . $data['filename'];
        $data['start_time'] = date("Y-m-d H:i:s", time());
        $file_ext = $file->extension();

        $exts = ['php', 'sql', 'html', 'css', 'js', 'jpg', 'png', 'gif', 'bmp', 'jpeg', 'svg', 'webp', 'ico', 'pdf'];
        // 校验文件格式
        if (in_array($file_ext, $exts) != 1) {
            exit(json_encode(['code' => 1, 'msg' => "暂不支持该格式文件上传"]));
        }

        if (Db::table('works')->where(['class_id' => $class['class_id'], 'filename' => $data['filename'], 'stu_no' => $data['stu_no'], 'work_id' => $data['work_id']])->find()) {
            // 修改操作
            $res = Db::table('works')->where(['class_id' => $class['class_id'], 'filename' => $data['filename'], 'stu_no' => $data['stu_no'], 'work_id' => $data['work_id']])->update($data);
            $savename = \think\facade\Filesystem::disk('public')->putFileAs($class['class_id'] . "/stu_work" . "/" . $data['work_id'] . '/' . $data['stu_no'], $file, $data['filename']);

            exit(json_encode(['code' => 0, 'msg' => "保存成功，请确认提交", "data" => ""]));
        } else {  //添加操作
            $res = Db::table('works')->insert($data);
            // 上传到本地服务器
            $savename = \think\facade\Filesystem::disk('public')->putFileAs($class['class_id'] . "/stu_work" . "/" . $data['work_id'] . '/' . $data['stu_no'], $file, $data['filename']);

            if ($savename) {
                exit(json_encode(['code' => 0, 'msg' => "保存成功，请确认提交", "data" => ""]));
            }
        }
        exit(json_encode(['code' => 1, 'msg' => "上传失败"]));
    }

    // 删除文件
    public function delfile()
    {
        $class = $this->class;
        $id = (int)Request::post('id', '');
        if ($id) {
            $res = Db::table('works')->where(['class_id' => $class['class_id'], 'id' => $id])->find();

            $file = new file();
            $file->unlink_file($res['work_url']);

            Db::table('works')->where(['class_id' => $class['class_id'], 'id' => $id])->delete();
            exit(json_encode(['code' => 0, 'msg' => "删除成功"]));
        }
        exit(json_encode(['code' => 1, 'msg' => "非法请求"]));
    }
}
