<?php

namespace app\admin\controller;

use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\View;
use file\Zip1;
use file\File;
use PhpOffice\PhpSpreadsheet\IOFactory; //用于载入已有的xlsx文件
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; //保存xlsx文件

/**
 * 成绩管理类
 * 学生信息管理
 */
class Score extends Base
{
    public function index()
    {
        $class_id = (int)Request::get('class_id', '');
        if (!$class_id) {
            exit(json_encode(['code' => 1, 'msg' => '非法请求'], true));
        }
        $data['class'] = Db::table('classes')->where('class_id', $class_id)->find();
        $data['admin'] = $this->admin;
        $data['class']['title'] = '成绩管理';

        $data['stu'] = Db::table('stu')->field('stu_no,stu_name')->where('class_id', $class_id)->order('stu_no', 'asc')->select()->toArray(); //获取所有的学生

        $data['work'] = Db::table('work')->where(['class_id' => $class_id, 'status' => 0])->select()->toArray();  //获取所有的作业

        $data['is_work'] = Db::table('is_work')->field('stu_no,work_id,is_true')->where('class_id', $class_id)->select()->toArray();

        $data['scores'] = Db::table('score')->field('stu_no,class_id,work_id,sum(score)  score_all')->where('class_id', $class_id)->group('stu_no,class_id,work_id')->select()->toArray();

        $work_count = Db::table('work')->where('status', 0)->count();  //作业次数
        $data['work_count'] = Db::table('work')->where('status', 0)->count();  //作业次数
        $stu_count = Db::table('stu')->count();  //学生人数

        foreach ($data['stu'] as $k => $v) {  //work\is_work\stu\score四表联查
            if ($data['work']) {  //存在作业的情况下，输出作业

                $data['stu'][$k]['work'] = $data['work']; //每个学生都有自己的作业

                foreach ($data['work'] as $kk => $vv) {
                    $data['stu'][$k]['work'][$kk]['score_all'] = 0;  //默认作业成绩为0

                    if ($data['is_work']) { //存在了is_work数据项

                        foreach ($data['is_work'] as $kkk => $vvv) {

                            if ($data['stu'][$k]['stu_no'] == $data['is_work'][$kkk]['stu_no'] && $data['stu'][$k]['work'][$kk]['work_id'] == $data['is_work'][$kkk]['work_id'] &&  $data['is_work'][$kkk]['is_true'] == 1) { //如果作业号、且学号匹配，则该学生有上传数据
                                foreach ($data['scores'] as $kkkk => $vvvv) {
                                    if ($data['stu'][$k]['stu_no'] == $data['scores'][$kkkk]['stu_no'] && $data['stu'][$k]['work'][$kk]['work_id'] == $data['scores'][$kkkk]['work_id']) {
                                        $score_all = number_format($data['scores'][$kkkk]['score_all'] / $stu_count, 2);
                                        $data['stu'][$k]['work'][$kk]['score_all'] = $score_all;
                                    }
                                }
                            }
                        }
                    }
                }
            } else {  //没有作业的情况下，输出一个空数组
                $data['stu'][$k]['work'] = [];
            }
        }

        foreach ($data['stu'] as $k => $v) {
            $data['stu'][$k]['score_alls'] = 0; //总成绩平均分

            foreach ($v['work'] as $kk => $vv) {
                $data['stu'][$k]['score_alls'] = number_format(($data['stu'][$k]['score_alls'] + $vv['score_all']), 2);
            }
        }
        $file = new file();
        if (file_exists('storage' . '/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx')) {
            $file->unlink_file('storage' . '/' . $class_id . '/stu_data' . '/' . $class_id . '_stu_data.xlsx');
        }
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->getCell('A' . 1)->setvalue('学号');
        $worksheet->getCell('B' . 1)->setvalue('姓名');
        $worksheet->getCell('C' . 1)->setvalue('平时成绩');
        for ($i = 0; $i < count($data['stu']); $i++) {  //循环出所有的数据
            $worksheet->getCell('A' . ($i + 2))->setvalue($data['stu'][$i]['stu_no']);
            $worksheet->getCell('B' . ($i + 2))->setvalue($data['stu'][$i]['stu_name']);
            $worksheet->getCell('C' . ($i + 2))->setvalue(number_format($data['stu'][$i]['score_alls'] / $work_count, 2));
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save('storage/' . $class_id . '/stu_score' . '/' . $class_id . '_stu_score.xlsx');

        View::engine()->layout('layout');
        return View::fetch('score/index', $data);
    }

    // 成绩导出
    public function get_zip()
    {
        $class_id = (int)Request::get('class_id', '');

        $zip1 = new Zip1();
        $zip1->zip($class_id, 'stu_score');
    }
}
