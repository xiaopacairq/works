<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>作业展示</title>
    <link rel="stylesheet" href="{__LAYUI_CSS_PATH__}">
    <script src="{__LAYUI_JS_PATH__}"></script>
</head>
<style>
    .container {
        margin: 20px auto;
        width: 80%;

    }
</style>

<body>
    <div class="container">

        <table class="layui-table">
            <thead>
                <tr>
                    <td>学号</td>
                    <td>状态</td>
                    <td>提交时间</td>
                    <td>评价人数</td>
                    <td>评价状态</td>
                    <td>操作</td>
                </tr>
            </thead>
            <tbody>
                {volist name='works' id='works'}
                <tr style='color:<?= $works["is_true"] != 1 ? "#ccc" : "" ?>'>
                    <td>{$works.stu_no}</td>
                    <td><?= $works['is_true'] == 1 ? "已上传" : ($works['is_true'] == 0 ? "未上传" : "未确认") ?></td>
                    <td>{$works.save_time}</td>
                    <td>{$works.remark_count}</td>
                    <td><?= (in_array($stu['stu_no'], array_column($works['remark_stu'], 'to_stu_no'))) == '' ? '<span style="color:red">未评价</span>' : '<span style="color:green">已评价</span>'
                        ?></td>
                    <td>
                        <button class="layui-btn layui-btn-primary layui-border-black layui-btn-xs <?= $works["is_true"] != 1 ? "layui-btn-disabled" : "" ?>" <?= $works["is_true"] != 1 ? "disabled" : "" ?>>
                            {if ( $works.is_true != 1) } <span>禁止访问</span>
                            {else /} <a href="/storage/{$works.class_id}/stu_work/{$works.work_id}/{$works.stu_no}" target="_blank">访问作业</a>
                            {/if}
                        </button>
                        <button class="layui-btn layui-btn-normal layui-btn-xs <?= $works["is_true"] != 1 ? "layui-btn-disabled" : "" ?>" <?= $works["is_true"] != 1 ? "disabled" : "" ?> onclick="to_remarks(' {$works.stu_no}','{$works.work_id}')">
                            <i class="layui-icon">&#xe609;</i>打分情况
                        </button>
                    </td>
                </tr>
                {/volist}
            </tbody>

    </div>
    <hr>
    <hr>
    <script>
        var upload = layui.upload; //上传
        var $ = layui.jquery; //上传
        var element = layui.element; //上传
        var layer = layui.layer; //上传

        // 评论方法
        function to_remarks(stu_no, work_id) {
            layer.open({
                type: 2,
                title: '作业评价',
                shade: 0.8,
                area: ['580px', '90%'],
                anim: 5,
                content: '/www/dis_work/remarks?stu_no=' + stu_no + '&work_id=' + work_id,
                cancel: function(index, layero) {
                    window.location.reload();
                }
            });
        }
    </script>
</body>

</html>