<?php

namespace app\admin\controller;

use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\View;
use file\File;
use file\Zip;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;



/**
 * 后台页面类
 * 
 * information 个人信息管理
 * index 班级信息主页
 * add 班级添加
 * edit 班级修改
 * del 班级删除
 */
class Home extends Base
{

    public function information()
    {
        if (Request::isPost()) { //修改数据
            $id = (int)Request::post('id', '');
            $data['pwd'] = Request::post('pwd', '');
            $data['email'] = Request::post('email', '');

            // 非空验证
            if ($data['pwd']  == '') {
                exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
            }
            if ($data['email']  == '') {
                exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
            }

            Db::table('admin')->where('id', $id)->update($data);

            return json_encode(['code' => 0, 'msg' => '保存成功,请重新登录'], true);
        } else { //页面展示
            $id = $this->admin['id'];
            $data['admin'] = Db::table('admin')->where('id', $id)->find();

            return View::fetch('/home/information', $data);
        }
    }

    public function index()
    {
        $data['admin'] = $this->admin;
        $data['classes'] = Db::table('classes')->select();

        return View::fetch('home/index', $data);
    }

    public function add()
    {
        if (Request::isPost()) {
            // 获取客户端数据
            $data['class_id'] = trim(Request::post('class_id', ''));
            $data['class_name'] = trim(Request::post('class_name', ''));
            $data['status'] = trim(Request::post('status', ''));
            $data['class_time'] = trim(Request::post('class_time', ''));
            $data['remarks'] = trim(Request::post('remarks', ''));

            // 非空验证
            if ($data['class_id']  == '') {
                exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
            }
            if ($data['class_name']  == '') {
                exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
            }

            // 添加的班级代码不能重复
            $res = Db::table('classes')->where('class_id', $data['class_id'])->find();
            if ($res) {
                exit(json_encode(['code' => 1, 'msg' => '班级代码已存在！'], true));
            }

            //表单长度填写限制
            if (mb_strlen($data['class_id']) > 30 || mb_strlen($data['class_name']) > 30 || mb_strlen($data['class_time']) > 30) {
                exit(json_encode(['code' => 1, 'msg' => '表单填写长度超出限制！'], true));
            }
            if (mb_strlen($data['remarks']) > 150) {
                exit(json_encode(['code' => 1, 'msg' => '备注填写长度超出限制！'], true));
            }
            $data['start_time'] = date("Y-m-d H:i:s", time());

            // 创建stu_data、stu_work、stu_score文件夹三个
            $file = new file();
            $res1 = $file->create_dir('storage' . '/' . $data['class_id'] . '/stu_data');
            $res2 = $file->create_dir('storage' . '/' . $data['class_id'] . '/stu_work');
            $res3 = $file->create_dir('storage' . '/'  . $data['class_id'] . '/stu_score');
            $res3 = $file->create_file('storage' . '/'  . $data['class_id'] . '/stu_work' . '/read.txt');


            // 创建stu_data.xlsx文件
            $spreadsheet_stu_data = new Spreadsheet();
            $spreadsheet_stu_data->getActiveSheet()->mergeCells('A1:D1');
            $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('D')->setWidth(10);
            $spreadsheet_stu_data->getActiveSheet()->getCell('A1')->setValue($data['class_id']);
            $spreadsheet_stu_data->getActiveSheet()->getCell('A2')->setValue('电子邮箱');
            $spreadsheet_stu_data->getActiveSheet()->getCell('B2')->setValue('学号');
            $spreadsheet_stu_data->getActiveSheet()->getCell('C2')->setValue('姓名');
            $spreadsheet_stu_data->getActiveSheet()->getCell('D2')->setValue('性别');
            $worksheet = $spreadsheet_stu_data->getActiveSheet();
            $writer_stu_data = new Xlsx($spreadsheet_stu_data);
            $writer_stu_data->save('storage' . '/' . $data['class_id'] . '/stu_data' . '/' . $data['class_id'] . '_stu_data.xlsx');


            // 创建stu_score.xlsx文件
            $spreadsheet_stu_score = new Spreadsheet();
            $spreadsheet_stu_score->getActiveSheet()->getCell('A1')->setValue('学号');
            $spreadsheet_stu_score->getActiveSheet()->getCell('B1')->setValue('姓名');
            $spreadsheet_stu_score->getActiveSheet()->getCell('C1')->setValue('平时成绩');
            $worksheet = $spreadsheet_stu_score->getActiveSheet();
            $writer_stu_score = new Xlsx($spreadsheet_stu_score);
            $writer_stu_score->save('storage' . '/' . $data['class_id'] . '/stu_score' . '/' . $data['class_id'] . '_stu_score.xlsx');


            if (!$res1) {
                exit(json_encode(['code' => 1, 'msg' => '添加失败'], true));
            }
            if (!$res2) {
                exit(json_encode(['code' => 1, 'msg' => '添加失败'], true));
            }
            if (!$res3) {
                exit(json_encode(['code' => 1, 'msg' => '添加失败'], true));
            }

            Db::table('classes')->insert($data);

            exit(json_encode(['code' => 0, 'msg' => '添加成功'], true));
        } else {
            return View::fetch('/home/add');
        }
    }

    public function edit()
    {
        if (Request::isPost()) {
            $id = (int)Request::post('id', '');
            $class_id = (int)Request::post('class_id', '');

            // 获取客户端数据
            $data['class_name'] = trim(Request::post('class_name', ''));
            $data['status'] = trim(Request::post('status', ''));
            $data['class_time'] = trim(Request::post('class_time', ''));
            $data['remarks'] = trim(Request::post('remarks', ''));

            if ($data['class_name']  == '') {
                exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
            }

            // 班级代码不能重复
            $res = Db::table('classes')->where('class_id', $class_id)->where('id', '<>', $id)->find();
            if ($res) {
                exit(json_encode(['code' => 1, 'msg' => '班级代码已存在！'], true));
            }

            //表单长度填写限制
            if (mb_strlen($class_id) > 30 || mb_strlen($data['class_name']) > 30 || mb_strlen($data['class_time']) > 30) {
                exit(json_encode(['code' => 1, 'msg' => '表单填写长度超出限制！'], true));
            }
            if (mb_strlen($data['remarks']) > 150) {
                exit(json_encode(['code' => 1, 'msg' => '备注填写长度超出限制！'], true));
            }
            $data['start_time'] = date("Y-m-d H:i:s", time());
            Db::table('classes')->where('id', $id)->update($data);
            exit(json_encode(['code' => 0, 'msg' => '修改成功'], true));
        } else {

            $id = (int)Request::get('id', '');

            $data['class'] = Db::table('classes')->where('id', $id)->find();
            return View::fetch('/home/edit', $data);
        }
    }

    public function del()
    {
        $id = (int)Request::post('id', '');
        $class_id = (int)Request::post('class_id', '');
        if (!$id) {
            exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
        }
        if (!$class_id) {
            exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
        }

        $file = new file();
        if (file_exists('storage/' . $class_id)) {
            $res = $file->remove_dir('storage/' . $class_id, true);
        }
        Db::table('classes')->where('class_id', $class_id)->delete();
        Db::table('score')->where('class_id', $class_id)->delete();
        Db::table('is_work')->where('class_id', $class_id)->delete();
        Db::table('works')->where('class_id', $class_id)->delete();
        Db::table('work')->where('class_id', $class_id)->delete();
        Db::table('stu')->where('class_id', $class_id)->delete();

        exit(json_encode(['code' => 0, 'msg' => '删除成功'], true));
    }

    public function get_zip()
    {
        $class_id = (int)Request::get('class_id', '');

        $zip = new Zip();
        $zip->zip($class_id);
    }
}
