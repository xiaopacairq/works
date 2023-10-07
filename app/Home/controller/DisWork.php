<?php

namespace app\home\controller;

use think\captcha\facade\Captcha;
use think\facade\Db;
use think\facade\Session;
use think\facade\Request;
use think\facade\View;


/**
 * 作业提交页面
 */
class DisWork extends Base
{
    public function index()
    {
        $data['class'] = $this->class;
        $data['stu'] = $this->stu;
        $data['class']['title'] = '作业展示';

        $data['work'] = Db::table('work')->where(['class_id' => $data['class']['class_id'], 'status' => 0])->order('work_id', 'desc')->select()->toArray();


        foreach ($data['work'] as $k => $v) {
            $data['work'][$k]['stu_num'] = Db::table('stu')->where(['class_id' => $data['class']['class_id']])->count();  //根据学生表查询学生总数

            $data['work'][$k]['is_true_stu_num'] = Db::table('is_work')
                ->where(['class_id' => $data['class']['class_id'], 'work_id' => $data['work'][$k]['work_id'], 'is_true' => 1])
                ->count();  //根据is_work的上传记录确定已经上传作业的名单
        }
        // exit(print_r($data)); //测试代码

        View::engine()->layout('layout');
        return View::fetch('dis_work/index', $data);
    }

    public function display()
    {
        $class = $this->class;
        $data['stu'] = $this->stu;
        $data['work_id'] = (int)Request::get('work_id', '');

        $data['works'] = Db::table('stu')->where(['class_id' => $class['class_id']])->select()->toArray();
        foreach ($data['works'] as $k => $v) {
            if (Db::table('is_work')->where(['class_id' => $class['class_id'],  'stu_no' => $v['stu_no'], 'work_id' => $data['work_id']])->find()) { //如果学生已点击
                if (Db::table('is_work')->where(['class_id' => $class['class_id'], 'stu_no' => $v['stu_no'], 'work_id' => $data['work_id'], 'is_true' => 1])->find()) { //如果学生点击作业且确认上传
                    $data['works'][$k]['is_true'] = 1;
                    $data['works'][$k]['work_id'] = $data['work_id'];
                    $data['works'][$k]['save_time'] = Db::table('is_work')->where(['class_id' => $class['class_id'], 'stu_no' => $v['stu_no'], 'work_id' => $data['work_id'], 'is_true' => 1])->find()['last_time']; //提交作业时间

                    $data['works'][$k]['remark_count'] = Db::table('score')->where(['class_id' => $class['class_id'], 'stu_no' => $v['stu_no'], 'work_id' => $data['work_id']])->count(); //评价人数为0

                    $data['works'][$k]['remark_stu'] = Db::table('score')->field('to_stu_no')->where(['class_id' => $class['class_id'], 'stu_no' => $v['stu_no'], 'work_id' => $data['work_id']])->select()->toArray(); //当前评价状态
                } else { //学生点击作业，但未上传
                    $data['works'][$k]['is_true'] = 0;
                    $data['works'][$k]['work_id'] = $data['work_id'];
                    $data['works'][$k]['save_time'] = 0; //提交作业时间
                    $data['works'][$k]['remark_count'] = 0; //评价人数为0
                    $data['works'][$k]['remark_stu'] = Db::table('score')->field('to_stu_no')->where(['class_id' => $class['class_id'], 'stu_no' => $v['stu_no'], 'work_id' => $data['work_id']])->select()->toArray(); //当前评价状态
                }
            } else { //如果学生未点击作业
                $data['works'][$k]['is_true'] = -1;
                $data['works'][$k]['work_id'] = $data['work_id'];
                $data['works'][$k]['save_time'] = 0; //提交作业时间
                $data['works'][$k]['remark_count'] = 0; //评价人数为0
                $data['works'][$k]['remark_stu'] = []; //当前评价状态未知
            }
        }
        return View::fetch('dis_work/display', $data);
    }

    public function remarks()
    {
        $class = $this->class;
        if (Request::isPost()) {
            $stu = $this->stu;
            $data['class_id'] = $class['class_id'];
            $data['work_id'] = (int)Request::post('work_id', '');
            $data['stu_no'] = (int)Request::post('stu_no', '');  //被评分的学号
            $data['to_stu_no'] =  $stu['stu_no'];  //参与评分的学号
            $data['score'] = (int)Request::post('score', '');
            $data['start_time'] = date("Y-m-d H:i:s", time());  //评分时间

            $res = Db::table('score')
                ->where([
                    'class_id' => $class['class_id'],
                    'work_id' => $data['work_id'],
                    'stu_no' => $data['stu_no'],
                    'to_stu_no' => $data['to_stu_no']
                ])->find();

            if ($res) { //如果已经评分过了，执行修改评分操作
                Db::table('score')->where([
                    'class_id' => $class['class_id'],
                    'work_id' => $data['work_id'],
                    'stu_no' => $data['stu_no'],
                    'to_stu_no' => $data['to_stu_no']
                ])->update($data);

                exit(json_encode(['code' => 0, 'msg' => "评分成功"]));
            } else { //如果没有评分过，执行添加评分记录
                Db::table('score')->insert($data);

                exit(json_encode(['code' => 0, 'msg' => "评分成功"]));
            }
            exit(json_encode(['code' => 1, 'msg' => "评分失败"]));
        } else {
            // 第一次请求页面
            $data['class_id'] = $class['class_id'];
            $data['stu'] = $this->stu;
            $data['work_id'] = Request::get('work_id', '');
            $data['stu_no'] = Request::get('stu_no', '');  //作业归宿者
            $data['to_stu_no'] =  $data['stu']['stu_no'];  //作业评价者
            $data['score'] = 30;
            $data['is_true'] = false; //提交状态，默认为false


            $data['remarks'] = Db::table('score')->where([
                'class_id' => $class['class_id'],
                'work_id' => $data['work_id'],
                'stu_no' => $data['stu_no'],
            ])->order('score', "desc")->select();

            $res = Db::table('score')
                ->where([
                    'class_id' => $class['class_id'],
                    'work_id' => $data['work_id'],
                    'stu_no' => $data['stu_no'],
                    'to_stu_no' => $data['to_stu_no']
                ])->find();

            if ($res) {
                $data['is_true'] = true; //已经提交过了
                $data['score'] = $res['score'];
            }
            // print_r($res);
            return View::fetch('dis_work/remarks', $data);
        }
    }
}
