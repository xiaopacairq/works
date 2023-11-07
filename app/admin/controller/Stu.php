<?php

namespace app\admin\controller;

use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\View;
use file\File;
use file\Zip1;
use PhpOffice\PhpSpreadsheet\IOFactory; //用于载入已有的xlsx文件
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; //保存xlsx文件
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * 后台页面类
 * 学生信息管理
 * index 学生信息主页
 * up_stu 学生一键导入页面   
 * upfile 执行文件导入 
 * send_email 发送邮件，支持单独发送和群发
 * add 支持单独添加学生信息
 * edit 学生信息修改
 * del 学生信息删除
 */
class Stu extends Base
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
        $data['class']['title'] = '学生管理';

        // 根据条件获取数据
        $data['search'] = trim(Request::get('search', ''));  //搜索内容
        $data['page'] = Request::get('page', '1');  //页数

        $where = [];
        if (!empty($data['search'])) {
            $where[] = ['stu_no', 'like', '%' . $data['search'] . '%'];
            $where[] = ['stu_name', 'like', '%' . $data['search'] . '%'];
            ($data['search'] == '男') ? $where[] = ['gender', '=', 0] : '';
            ($data['search'] == '女') ? $where[] = ['gender', '=', 1] : '';
        }

        // $sql = Db::table('stu')->where(function ($query) use ($where) {
        //     $query->whereOr($where);
        // })->where('class_id', $class_id)->order('stu_no', 'asc')->fetchsql(true)->select();
        // dump($where);
        // dump($sql);
        // 获取学生数据

        $data['stu'] = Db::table('stu')->where('class_id', $class_id)->where(function ($query) use ($where) {
            $query->whereOr($where);
        })->order('stu_no', 'asc')->paginate([
            'list_rows' => 8,
            'query' => request()->param(),
        ]);
        $data['page'] = $data['stu']->render();

        // 清空文件--------------
        // 删除原文件
        $file = new file();
        if (file_exists('storage' . '/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx')) {
            $file->unlink_file('storage' . '/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx');
        }

        $data_stu = Db::table('stu')->field('stu_no,email,stu_name,gender')->where('class_id', $class_id)->order('stu_no', 'asc')->select()->toArray();

        // 创建新的stu_data.xlsx文件
        $spreadsheet_stu_data = new Spreadsheet();
        $spreadsheet_stu_data->getActiveSheet()->mergeCells('A1:D1');
        $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $spreadsheet_stu_data->getActiveSheet()->getCell('A1')->setValue($class_id);
        $spreadsheet_stu_data->getActiveSheet()->getCell('A2')->setValue('电子邮箱');
        $spreadsheet_stu_data->getActiveSheet()->getCell('B2')->setValue('学号');
        $spreadsheet_stu_data->getActiveSheet()->getCell('C2')->setValue('姓名');
        $spreadsheet_stu_data->getActiveSheet()->getCell('D2')->setValue('性别');

        $worksheet_stu_data = $spreadsheet_stu_data->getActiveSheet(); //读取excel文件
        $worksheet_stu_data->getCell('A1')->setValue($class_id);
        $worksheet_stu_data->getCell('A2')->setValue('电子邮箱');
        $worksheet_stu_data->getCell('B2')->setValue('学号');
        $worksheet_stu_data->getCell('C2')->setValue('姓名');
        $worksheet_stu_data->getCell('D2')->setValue('性别');
        for ($i = 0; $i < count($data_stu); $i++) {  //循环出excel所有的数据，并进行校验
            $worksheet_stu_data->setCellValue('A' . ($i + 3), $data_stu[$i]['email']);
            $worksheet_stu_data->setCellValue('B' . ($i + 3), $data_stu[$i]['stu_no']);
            $worksheet_stu_data->setCellValue('C' . ($i + 3), $data_stu[$i]['stu_name']);
            $worksheet_stu_data->setCellValue('D' . ($i + 3), $data_stu[$i]['gender'] == 0 ? "男" : "女");
        }
        $writer_stu_data = new Xlsx($spreadsheet_stu_data);
        $writer_stu_data->save('storage/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx');

        View::engine()->layout('layout');
        return View::fetch('stu/index', $data);
    }

    // 文件上传页面
    public function up_stu()
    {
        $data['class_id'] = (int)Request::get('class_id', '');

        return View::fetch('stu/up_stu', $data);
    }

    //学生excel维护和mysql数据表同步接口
    public function upfile() //导入学生的学号不能重复、班级代码要和登入的系统一致
    {
        $class_id = (int)Request::param('class_id', '');
        $fileclass = new File(); //调用extend内的file类（文件操作）
        $exts = ['xlsx'];  //限制文件格式


        // //从前端获取文件上传信息
        $file = Request::file('file');
        $file_ext = $file->extension();

        // 校验文件格式
        if (in_array($file_ext, $exts) != 1) {
            exit(json_encode(['code' => 1, 'msg' => "仅支持:xls、xlsx格式"]));
        }

        $spreadsheet = IOFactory::load($file); //载入xlsx文件
        $worksheet = $spreadsheet->getActiveSheet(); //读取excel文件
        $excel_class_id = $worksheet->getCell('A1')->getvalue();  //获取班级代码
        if ($excel_class_id != $class_id) {
            exit(json_encode(['code' => 1, 'msg' => "导入文件的班级代码不匹配，请重试！"]));
        }

        $row_count = $worksheet->getHighestRow(); //读取excel最大行数，即学生的数量 = row_Count - 前两个标题行
        if ($row_count < 3) {
            exit(json_encode(['code' => 1, 'msg' => "表格无数据"]));
        }

        for ($i = 3; $i <= $row_count; $i++) {  //循环出excel所有的数据，并进行校验
            if ($worksheet->getCell('A' . $i)->getvalue() == "") {
                exit(json_encode(['code' => 1, 'msg' => "邮箱出现空行，请重新上传"]));
            }

            $patt_email = "/^[A-Za-z0-9\u4e00-\u9fa5]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/";
            if (!(preg_match($patt_email, $worksheet->getCell('A' . $i)->getvalue()))) { //如果不匹配邮箱
                exit(json_encode(['code' => 1, 'msg' => "第 $i 行的邮箱格式错误"]));
            }

            if ($worksheet->getCell('B' . $i)->getvalue() == "") {
                exit(json_encode(['code' => 1, 'msg' => "学号出现空行，请重新上传"]));
            }
            if ($worksheet->getCell('C' . $i)->getvalue() == "") {
                exit(json_encode(['code' => 1, 'msg' => "姓名出现空行，请重新上传"]));
            }
            if ($worksheet->getCell('D' . $i)->getvalue() == "") {
                exit(json_encode(['code' => 1, 'msg' => "性别出现空行，请重新上传"]));
            }
            if ($worksheet->getCell('D' . $i)->getvalue() != "男" && $worksheet->getCell('D' . $i)->getvalue() != "女") {
                exit(json_encode(['code' => 1, 'msg' => "第 $i 行的性别格式错误"]));
            }

            $data['stu'][$i - 3]['class_id'] = $class_id;
            $data['stu'][$i - 3]['email'] = $worksheet->getCell('A' . $i)->getvalue();
            $data['stu'][$i - 3]['stu_no'] = $worksheet->getCell('B' . $i)->getvalue();
            $data['stu'][$i - 3]['stu_name'] = $worksheet->getCell('C' . $i)->getvalue();
            $data['stu'][$i - 3]['gender'] = ($worksheet->getCell('D' . $i)->getvalue() == "男") ? "0" : "1";

            //    密码随机生成8位
            $characters = '1234567890abcdefghijklmnopqrstuvwxyz';
            $data['stu'][$i - 3]['stu_pwd'] = '';
            for ($j = 0; $j < 8; $j++) {
                $index = rand(0, strlen($characters) - 1);
                $data['stu'][$i - 3]['stu_pwd'] .= $characters[$index];
            }
            $data['stu'][$i - 3]['add_time'] = date('Y-m-d H:i:s', time());
            $data['stu'][$i - 3]['last_time'] = date("Y-m-d H:i:s", time());
        }

        //根据数据库的数据将学生更新到excel表格中
        $spreadsheet_stu_data = IOFactory::load('storage/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx');
        $worksheet_stu_data = $spreadsheet_stu_data->getActiveSheet(); //读取excel文件
        $worksheet_stu_data->getCell('A1')->setValue($class_id);
        $worksheet_stu_data->getCell('A2')->setValue('电子邮箱');
        $worksheet_stu_data->getCell('B2')->setValue('学号');
        $worksheet_stu_data->getCell('C2')->setValue('姓名');
        $worksheet_stu_data->getCell('D2')->setValue('性别');
        for ($i = 3; $i <= $row_count; $i++) {  //循环出excel所有的数据，并进行校验
            $worksheet_stu_data->setCellValue('A' . $i, $data['stu'][$i - 3]['email']);
            $worksheet_stu_data->setCellValue('B' . $i, $data['stu'][$i - 3]['stu_no']);
            $worksheet_stu_data->setCellValue('C' . $i, $data['stu'][$i - 3]['stu_name']);
            $worksheet_stu_data->setCellValue('D' . $i, $data['stu'][$i - 3]['gender'] == 0 ? "男" : "女");
        }
        $writer_stu_data = new Xlsx($spreadsheet_stu_data);
        $writer_stu_data->save('storage/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx');

        $res =  Db::table('stu')->insertAll($data['stu']); // 添加新的学生数据
        if (!$res) {
            exit(json_encode(['code' => 1, 'msg' => "上传失败!!!"]));
        }

        exit(json_encode(['code' => 0, 'msg' => "学生导入成功"]));
    }

    //邮箱发送接口
    public function send_email()
    {
        $class_id = (int)Request::post('class_id', '');
        $class_name = Request::post('class_name', '');
        $stu_no = (int)Request::post('stu_no', ''); // 单发标识
        $is_all = (int)Request::post('is_all', ''); //群发标识
        $admin = $this->admin;  //用户信息

        if ($class_id == '') {
            exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
        }
        if ($class_name == '') {
            exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
        }


        if ($stu_no) { //单独发送密码
            $stu = Db::table('stu')->where(['class_id' => $class_id, 'stu_no' => $stu_no])->find();
            $mail = new PHPMailer(true);  //开启邮箱发送

            try {
                //服务器配置
                $mail->CharSet = "UTF-8";                     //设定邮件编码
                $mail->SMTPDebug = 0;                        // 调试模式输出
                $mail->isSMTP();                             // 使用SMTP
                $mail->Host = $admin['email_system'];                // SMTP服务器
                $mail->SMTPAuth = true;                      // 允许 SMTP 认证
                $mail->Username = $admin['email'];  // SMTP 用户名  即邮箱的用户名
                $mail->Password = $admin['check_code'];         // SMTP 密码  部分邮箱是授权码(例如163邮箱)
                $mail->SMTPSecure = 'ssl';                    // 允许 TLS 或者ssl协议
                $mail->Port = 465;                            // 服务器端口 25 或者465 具体要看邮箱服务器支持

                $mail->setFrom($admin['email'], $class_name);  //发件人
                $mail->addAddress($stu['email'], $stu['stu_name']);  // 收件人
                //$mail->addAddress('ellen@example.com');  // 可添加多个收件人
                $mail->addReplyTo($admin['email'], $class_name); //回复的时候回复给哪个邮箱 建议和发件人一致

                //Content
                $mail->isHTML(true);                                  // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
                $mail->Subject = $stu['stu_name'] . '你好！智慧作业上传评分系统门户【' . $class_name . '】';
                $mail->Body    = '<h1>智慧作业上传评分系统门户</h1><p>网站地址：http://43.136.39.218:3002/</p><p>班级代码：' . $stu['class_id'] . '</p><p>账号：' . $stu['stu_no'] . '</p><p>密码：' . $stu['stu_pwd'] . '</p>';
                $mail->AltBody = '<p>网站地址：http://43.136.39.218:3002/</p><p>班级代码：' . $stu['class_id'] . '</p><p>账号：' . $stu['stu_no'] . '</p><p>密码：' . $stu['stu_pwd'] . '</p>';

                $mail->send();
                exit(json_encode(['code' => 0, 'msg' => "邮件发送成功"]));
            } catch (Exception $e) {
                exit(json_encode(['code' => 1, 'msg' => '邮件发送失败' . $mail->ErrorInfo]));
            }
        }
        if ($is_all == 1) {
            $stu_data = Db::table('stu')->where('class_id', $class_id)->select()->toArray();
            foreach ($stu_data as $v) {
                $mail = new PHPMailer(true);  //开启邮箱发送

                try {
                    //服务器配置
                    $mail->CharSet = "UTF-8";                     //设定邮件编码
                    $mail->SMTPDebug = 0;                        // 调试模式输出
                    $mail->isSMTP();                             // 使用SMTP
                    $mail->Host = $admin['email_system'];                // SMTP服务器
                    $mail->SMTPAuth = true;                      // 允许 SMTP 认证
                    $mail->Username = $admin['email'];  // SMTP 用户名  即邮箱的用户名
                    $mail->Password = $admin['check_code'];         // SMTP 密码  部分邮箱是授权码(例如163邮箱)
                    $mail->SMTPSecure = 'ssl';                    // 允许 TLS 或者ssl协议
                    $mail->Port = 465;                            // 服务器端口 25 或者465 具体要看邮箱服务器支持

                    $mail->setFrom($admin['email'],  $class_name);  //发件人
                    $mail->addReplyTo($admin['email'], $class_name); //回复的时候回复给哪个邮箱 建议和发件人一致

                    $mail->addAddress($v['email'], $v['stu_name']);  // 收件人
                    $mail->isHTML(true);   // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
                    $mail->Subject = $v['stu_name'] . '你好！智慧作业上传评分系统门户【' . $class_name . '】';
                    $mail->Body    = '<h1>智慧作业上传评分系统门户</h1><p>班级代码：' . $v['class_id'] . '</p><p>账号：' . $v['stu_no'] . '</p><p>密码：' . $v['stu_pwd'] . '</p><p>网站地址：http://43.136.39.218:3002/</p>';
                    $mail->AltBody = '<p>网站地址：http://43.136.39.218:3002/</p><p>班级代码：' . $v['class_id'] . '</p><p>账号：' . $v['stu_no'] . '</p><p>密码：' . $v['stu_pwd'] . '</p>';
                    $mail->send();
                } catch (Exception $e) {
                    exit(json_encode(['code' => 1, 'msg' => '邮件发送失败' . $mail->ErrorInfo]));
                }
            }
            exit(json_encode(['code' => 0, 'msg' => "邮件发送成功"]));
        }
    }

    //学生单独添加
    public function add()
    {
        if (Request::isPost()) {
            $class_id = (int)Request::post('class_id', '');
            $data['class_id'] = (int)Request::post('class_id', '');
            $data['stu_no'] = (int)Request::post('stu_no', '');
            $data['stu_name'] = trim(Request::post('stu_name', ''));
            $data['email'] = trim(Request::post('email', ''));
            $data['gender'] = (int)Request::post('gender', '');

            if ($data['stu_no'] == '') {
                exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
            }
            if ($data['stu_name'] == '') {
                exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
            }
            if ($data['class_id'] == '') {
                exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
            }
            if ($data['email'] == '') {
                exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
            }
            $patt_email = "/^[A-Za-z0-9\u4e00-\u9fa5]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/";
            if (!(preg_match($patt_email, $data['email']))) { //如果不匹配邮箱
                exit(json_encode(['code' => 1, 'msg' => "邮箱格式错误"]));
            }

            $res = Db::table('stu')->where(['stu_no' => $data['stu_no'], 'class_id' => $data['class_id']])->find();
            if ($res) {
                exit(json_encode(['code' => 1, 'msg' => "学号已经存在"]));
            }

            // 密码随机生成8位
            $characters = '1234567890abcdefghijklmnopqrstuvwxyz';
            $data['stu_pwd'] = '';
            for ($i = 0; $i < 8; $i++) {
                $index = rand(0, strlen($characters) - 1);
                $data['stu_pwd'] .= $characters[$index];
            }
            $data['add_time'] = date('Y-m-d H:i:s', time());
            $data['last_time'] = date("Y-m-d H:i:s", time());



            // 清空文件--------------
            // 删除原文件
            $file = new file();
            if (file_exists('storage' . '/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx')) {
                $file->unlink_file('storage' . '/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx');
            }

            $data_stu = Db::table('stu')->field('stu_no,email,stu_name,gender')->where('class_id', $class_id)->order('stu_no', 'asc')->select()->toArray();

            // 创建新的stu_data.xlsx文件
            $spreadsheet_stu_data = new Spreadsheet();
            $spreadsheet_stu_data->getActiveSheet()->mergeCells('A1:D1');
            $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('D')->setWidth(10);
            $spreadsheet_stu_data->getActiveSheet()->getCell('A1')->setValue($class_id);
            $spreadsheet_stu_data->getActiveSheet()->getCell('A2')->setValue('电子邮箱');
            $spreadsheet_stu_data->getActiveSheet()->getCell('B2')->setValue('学号');
            $spreadsheet_stu_data->getActiveSheet()->getCell('C2')->setValue('姓名');
            $spreadsheet_stu_data->getActiveSheet()->getCell('D2')->setValue('性别');

            $worksheet_stu_data = $spreadsheet_stu_data->getActiveSheet(); //读取excel文件
            $worksheet_stu_data->getCell('A1')->setValue($class_id);
            $worksheet_stu_data->getCell('A2')->setValue('电子邮箱');
            $worksheet_stu_data->getCell('B2')->setValue('学号');
            $worksheet_stu_data->getCell('C2')->setValue('姓名');
            $worksheet_stu_data->getCell('D2')->setValue('性别');
            for ($i = 0; $i < count($data_stu); $i++) {  //循环出excel所有的数据，并进行校验
                $worksheet_stu_data->setCellValue('A' . ($i + 3), $data_stu[$i]['email']);
                $worksheet_stu_data->setCellValue('B' . ($i + 3), $data_stu[$i]['stu_no']);
                $worksheet_stu_data->setCellValue('C' . ($i + 3), $data_stu[$i]['stu_name']);
                $worksheet_stu_data->setCellValue('D' . ($i + 3), $data_stu[$i]['gender'] == 0 ? "男" : "女");
            }
            $writer_stu_data = new Xlsx($spreadsheet_stu_data);
            $writer_stu_data->save('storage/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx');

            $res = Db::table('stu')->insert($data);
            if ($res) {
                exit(json_encode(['code' => 0, 'msg' => '保存成功'], true));
            }
            exit(json_encode(['code' => 1, 'msg' => '保存失败'], true));
        } else {
            $data['class_id'] = (int)Request::get('class_id', '');
            return View::fetch('stu/add', $data);
        }
    }

    // 修改
    public function edit()
    {
        if (Request::isPost()) {
            $stu_no = (int)Request::post('stu_no', ''); //学号默认不能修改
            $class_id = (int)Request::post('class_id', '');
            $data['stu_name'] = trim(Request::post('stu_name', ''));
            $data['gender'] = (int)Request::post('gender', '');
            $data['stu_pwd'] = Request::post('stu_pwd', '');
            $data['email'] = trim(Request::post('email', ''));

            if (empty($stu_no)) { //如果没有get参数，则返回
                exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
            }
            if (empty($class_id)) { //如果没有get参数，则返回
                exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
            }

            if ($data['stu_name'] == '') {
                exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
            }
            if ($data['stu_pwd'] == '') {
                exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
            }
            if ($data['email'] == '') {
                exit(json_encode(['code' => 1, 'msg' => '必填项不能为空'], true));
            }
            $patt_email = "/^[A-Za-z0-9\u4e00-\u9fa5]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/";
            if (!(preg_match($patt_email, $data['email']))) { //如果不匹配邮箱
                exit(json_encode(['code' => 1, 'msg' => "邮箱格式错误"]));
            }

            Db::table('stu')->where(['stu_no' => $stu_no, 'class_id' => $class_id])->update($data);

            // 清空文件--------------
            // 删除原文件
            $file = new file();
            $file->unlink_file('storage' . '/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx');

            $data_stu = Db::table('stu')->field('stu_no,email,stu_name,gender')->where('class_id', $class_id)->order('stu_no', 'asc')->select()->toArray();

            // 创建新的stu_data.xlsx文件
            $spreadsheet_stu_data = new Spreadsheet();
            $spreadsheet_stu_data->getActiveSheet()->mergeCells('A1:D1');
            $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('D')->setWidth(10);
            $spreadsheet_stu_data->getActiveSheet()->getCell('A1')->setValue($class_id);
            $spreadsheet_stu_data->getActiveSheet()->getCell('A2')->setValue('电子邮箱');
            $spreadsheet_stu_data->getActiveSheet()->getCell('B2')->setValue('学号');
            $spreadsheet_stu_data->getActiveSheet()->getCell('C2')->setValue('姓名');
            $spreadsheet_stu_data->getActiveSheet()->getCell('D2')->setValue('性别');

            $worksheet_stu_data = $spreadsheet_stu_data->getActiveSheet(); //读取excel文件
            $worksheet_stu_data->getCell('A1')->setValue($class_id);
            $worksheet_stu_data->getCell('A2')->setValue('电子邮箱');
            $worksheet_stu_data->getCell('B2')->setValue('学号');
            $worksheet_stu_data->getCell('C2')->setValue('姓名');
            $worksheet_stu_data->getCell('D2')->setValue('性别');
            for ($i = 0; $i < count($data_stu); $i++) {  //循环出excel所有的数据，并进行校验
                $worksheet_stu_data->setCellValue('A' . ($i + 3), $data_stu[$i]['email']);
                $worksheet_stu_data->setCellValue('B' . ($i + 3), $data_stu[$i]['stu_no']);
                $worksheet_stu_data->setCellValue('C' . ($i + 3), $data_stu[$i]['stu_name']);
                $worksheet_stu_data->setCellValue('D' . ($i + 3), $data_stu[$i]['gender'] == 0 ? "男" : "女");
            }
            $writer_stu_data = new Xlsx($spreadsheet_stu_data);
            $writer_stu_data->save('storage/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx');

            exit(json_encode(['code' => 0, 'msg' => '保存成功'], true));
        } else {
            $stu_no =  (int)Request::get('stu_no', '');
            $class_id = (int)Request::get('class_id', '');
            if (empty($stu_no)) { //如果没有get参数，则返回
                exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
            }
            if (empty($class_id)) { //如果没有get参数，则返回
                exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
            }
            $data = Db::table('stu')->where(['stu_no' => $stu_no, 'class_id' => $class_id])->find();
            return View::fetch('stu/edit', $data);
        }
    }


    // 学生导出
    public function get_zip()
    {
        $class_id = (int)Request::get('class_id', '');

        $zip1 = new Zip1();
        $zip1->zip($class_id, 'stu_data');
    }

    // 删除
    public function del()
    {
        if (Request::isPost()) {
            //删除学生的stu、score、is_work、works
            $stu_no = (int)Request::post('stu_no', '');  //获取到学号
            $class_id = (int)Request::post('class_id', '');  //获取到学号
            $is_clear_all = (int)Request::post('is_clear_all', '');  //获取到清空权限，则删除整个数据表里的数据

            if (empty($class_id)) { //如果没有get参数，则返回
                exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
            }

            if ($is_clear_all) { //清空所有数据
                // 清空文件--------------
                // 删除原文件
                $file = new file();
                if (file_exists('storage' . '/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx'))
                    $file->unlink_file('storage' . '/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx');

                // 创建新的stu_data.xlsx文件
                $spreadsheet_stu_data = new Spreadsheet();
                $spreadsheet_stu_data->getActiveSheet()->mergeCells('A1:D1');
                $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('D')->setWidth(10);
                $spreadsheet_stu_data->getActiveSheet()->getCell('A1')->setValue($class_id);
                $spreadsheet_stu_data->getActiveSheet()->getCell('A2')->setValue('电子邮箱');
                $spreadsheet_stu_data->getActiveSheet()->getCell('B2')->setValue('学号');
                $spreadsheet_stu_data->getActiveSheet()->getCell('C2')->setValue('姓名');
                $spreadsheet_stu_data->getActiveSheet()->getCell('D2')->setValue('性别');
                $worksheet = $spreadsheet_stu_data->getActiveSheet();
                $writer_stu_data = new Xlsx($spreadsheet_stu_data);
                $writer_stu_data->save('storage' . '/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx');

                Db::table('stu')->where('class_id', $class_id)->delete();
                Db::table('works')->where('class_id', $class_id)->delete();
                Db::table('is_work')->where('class_id', $class_id)->delete();
                Db::table('score')->where('class_id', $class_id)->delete();

                exit(json_encode(['code' => 0, 'msg' => '清空数据成功'], true));
            } else {  //删除单条数据

                if (empty($stu_no)) { //如果没有get参数，则返回
                    exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
                }

                Db::table('stu')->where(['stu_no' => $stu_no, 'class_id' => $class_id])->delete();
                Db::table('score')->where(['stu_no' => $stu_no, 'class_id' => $class_id])->delete();
                Db::table('is_work')->where(['stu_no' => $stu_no, 'class_id' => $class_id])->delete();
                Db::table('works')->where(['stu_no' => $stu_no, 'class_id' => $class_id])->delete();

                // 清空文件--------------
                // 删除原文件
                $file = new file();
                $file->unlink_file('storage' . '/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx');

                $data_stu = Db::table('stu')->field('stu_no,email,stu_name,gender')->where('class_id', $class_id)->order('stu_no', 'asc')->select()->toArray();

                // 创建新的stu_data.xlsx文件
                $spreadsheet_stu_data = new Spreadsheet();
                $spreadsheet_stu_data->getActiveSheet()->mergeCells('A1:D1');
                $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $spreadsheet_stu_data->getActiveSheet()->getColumnDimension('D')->setWidth(10);
                $spreadsheet_stu_data->getActiveSheet()->getCell('A1')->setValue($class_id);
                $spreadsheet_stu_data->getActiveSheet()->getCell('A2')->setValue('电子邮箱');
                $spreadsheet_stu_data->getActiveSheet()->getCell('B2')->setValue('学号');
                $spreadsheet_stu_data->getActiveSheet()->getCell('C2')->setValue('姓名');
                $spreadsheet_stu_data->getActiveSheet()->getCell('D2')->setValue('性别');

                $worksheet_stu_data = $spreadsheet_stu_data->getActiveSheet(); //读取excel文件
                $worksheet_stu_data->getCell('A1')->setValue($class_id);
                $worksheet_stu_data->getCell('A2')->setValue('电子邮箱');
                $worksheet_stu_data->getCell('B2')->setValue('学号');
                $worksheet_stu_data->getCell('C2')->setValue('姓名');
                $worksheet_stu_data->getCell('D2')->setValue('性别');
                for ($i = 3; $i <= count($data_stu); $i++) {  //循环出excel所有的数据，并进行校验
                    $worksheet_stu_data->setCellValue('A' . $i, $data_stu[$i - 3]['email']);
                    $worksheet_stu_data->setCellValue('B' . $i, $data_stu[$i - 3]['stu_no']);
                    $worksheet_stu_data->setCellValue('C' . $i, $data_stu[$i - 3]['stu_name']);
                    $worksheet_stu_data->setCellValue('D' . $i, $data_stu[$i - 3]['gender'] == 0 ? "男" : "女");
                }
                $writer_stu_data = new Xlsx($spreadsheet_stu_data);
                $writer_stu_data->save('storage/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx');
                exit(json_encode(['code' => 0, 'msg' => '删除成功'], true));
            }
        } else {
            exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
        }
    }


    // 生成excel表
    // public function reset_excel()
    // {
    //     $fileclass = new File(); //调用extend内的file类（文件操作）

    //     //读取excel表格的内容
    //     $file = "storage/2005/stu_data/2005班录入名单.xls";

    //     $spreadsheet = IOFactory::load($file); //载入xlsx文件

    //     $worksheet = $spreadsheet->getActiveSheet();

    //     $data['class_id'] = $worksheet->getCell('A1')->getvalue();
    //     $row_count = $worksheet->getHighestRow(); //最大行数,数据量

    //     for ($i = 3; $i <= $row_count; $i++) {  //循环出所有的数据
    //         if ($worksheet->getCell('A' . $i)->getvalue() == "") {
    //             $fileclass->unlink_file($file);  //删除文件
    //             exit(json_encode(['code' => 1, 'msg' => "邮箱出现空行，请重新上传"]));
    //         }

    //         $patt_email = "/^[A-Za-z0-9\u4e00-\u9fa5]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/";
    //         if (!(preg_match($patt_email, $worksheet->getCell('A' . $i)->getvalue()))) { //如果不匹配邮箱
    //             exit(json_encode(['code' => 1, 'msg' => "第 $i 行的邮箱格式错误"]));
    //         }

    //         if ($worksheet->getCell('B' . $i)->getvalue() == "") {
    //             $fileclass->unlink_file($file);  //删除文件
    //             exit(json_encode(['code' => 1, 'msg' => "学号出现空行，请重新上传"]));
    //         }
    //         if ($worksheet->getCell('C' . $i)->getvalue() == "") {
    //             $fileclass->unlink_file($file);  //删除文件
    //             exit(json_encode(['code' => 1, 'msg' => "姓名出现空行，请重新上传"]));
    //         }
    //         if ($worksheet->getCell('D' . $i)->getvalue() == "") {
    //             $fileclass->unlink_file($file);  //删除文件
    //             exit(json_encode(['code' => 1, 'msg' => "性别出现空行，请重新上传"]));
    //         }

    //         if ($worksheet->getCell('D' . $i)->getvalue() != "男" && $worksheet->getCell('D' . $i)->getvalue() != "女") {
    //             exit(json_encode(['code' => 1, 'msg' => "第 $i 行的性别格式错误"]));
    //         }

    //         $data['stu'][$i - 3]['email'] = $worksheet->getCell('A' . $i)->getvalue();
    //         $data['stu'][$i - 3]['stu_no'] = $worksheet->getCell('B' . $i)->getvalue();
    //         $data['stu'][$i - 3]['stu_name'] = $worksheet->getCell('C' . $i)->getvalue();
    //         $data['stu'][$i - 3]['gender'] = ($worksheet->getCell('D' . $i)->getvalue() == "男") ? "0" : "1";

    //         //    密码随机生成8位
    //         $characters = '1234567890abcdefghijklmnopqrstuvwxyz';
    //         $data['stu'][$i - 3]['stu_pwd'] = '';
    //         for ($j = 0; $j < 8; $j++) {
    //             $index = rand(0, strlen($characters) - 1);
    //             $data['stu'][$i - 3]['stu_pwd'] .= $characters[$index];
    //         }
    //         $data['stu'][$i - 3]['add_time'] = date('Y-m-d H:i:s', time());
    //         $data['stu'][$i - 3]['last_time'] = date("Y-m-d H:i:s", time());
    //     }

    //     Db::table('stu')->insertAll($data['stu']);
    // }
}