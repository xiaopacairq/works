<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>作业布置</title>
    <link rel="stylesheet" href="{__LAYUI_CSS_PATH__}">
    <script src="{__LAYUI_JS_PATH__}"></script>
</head>

<body style="padding: 10px;">
    <input type="hidden" name="id" value="{$work.id}">
    <div class="layui-form layui-form-pane">
        <div class="layui-form-item">
            <label class="layui-form-label">作业次数</label>
            <div class="layui-input-block">
                <input type="text" name="work_id" autocomplete="on" placeholder="例：1、2、3" class="layui-input"
                    value="{$work.work_id}" disabled>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">截止时间</label>
            <div class="layui-input-block">
                <input type="text" name="work_last_time" autocomplete="off" class="layui-input" id="date"
                    value="{$work.work_last_time}">
            </div>
        </div>
        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">作业介绍</label>
            <div class="layui-input-block">
                <textarea placeholder="请输入作业要求" class="layui-textarea"
                    name="work_remarks"> {$work.work_remarks}</textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">作业状态</label>
            <div class="layui-input-block">
                <input type="checkbox" name="status" lay-skin="switch" lay-text="开启|关闭" {$work.status==0?"checked":""}>
            </div>
        </div>
        <div class="layui-form-item">
            <button class="layui-btn layui-btn-normal" onclick="save('{$work.class_id}')">保存</button>
            <button class="layui-btn  layui-btn-danger"
                onclick='del("{$work.class_id}","{$work.id}","{$work.work_id}")'>删除</button>
        </div>
        <hr>
        <table class="layui-table">
            <thead>
                <tr>
                    <td>学号</td>
                    <td>状态</td>
                    <td>提交时间</td>
                    <td>作业成绩</td>
                    <td>操作</td>
                </tr>
            </thead>
            <tbody>
                {volist name='works' id='works'}
                <tr style='color:<?= $works["is_true"] != 1 ? "#ccc" : "" ?>'>
                    <td>{$works.stu_no}</td>
                    <td><?= $works['is_true'] == 1 ? "已上传" : ($works['is_true'] == 0 ? "未上传" : "未确认") ?></td>
                    <td>{$works.save_time}</td>
                    <td>{$works.all_score}</td>
                    <td>
                        <button
                            class="layui-btn layui-btn-primary layui-border-black layui-btn-xs <?= $works["is_true"] != 1 ? "layui-btn-disabled" : "" ?>"
                            <?= $works["is_true"] != 1 ? "disabled" : "" ?>>
                            {if ( $works.is_true != 1) } <span>禁止访问</span>
                            {else /} <a href="/storage/{$works.class_id}/stu_work/{$works.work_id}/{$works.stu_no}"
                                target="_blank">访问作业</a>
                            {/if}
                        </button>
                        <button
                            class="layui-btn layui-btn-normal layui-btn-xs <?= $works["is_true"] != 1 ? "layui-btn-disabled" : "" ?>"
                            <?= $works["is_true"] != 1 ? "disabled" : "" ?>
                            onclick="detail('{$works.class_id}','{$works.stu_no}','{$works.work_id}')">
                            <i class="layui-icon">&#xe609;</i>打分情况
                        </button>
                    </td>
                </tr>
                {/volist}
            </tbody>
        </table>
    </div>
    <script>
    var laydate = layui.laydate;
    var $ = layui.jquery;

    //执行一个laydate实例
    laydate.render({
        elem: '#date', //指定元素
        type: 'datetime',
    });

    function save(class_id) {
        var id = $('input[name="id"]').val();
        var work_last_time = $('input[name="work_last_time"]').val();
        var work_remarks = $('textarea[name="work_remarks"]').val();
        var status = $('input[name="status"]').is(":checked") ? 0 : 1;

        if (work_last_time == '' || work_remarks == '') {
            layer.msg('必填项不能为空', {
                icon: 2
            })
        } else {
            $.post('/houtai/work/edit', {
                id,
                class_id,
                work_last_time,
                work_remarks,
                status,
            }, function(res) {
                if (res.code > 0) {
                    layer.msg(res.msg, {
                        icon: 2
                    })
                } else {
                    layer.msg(res.msg, {
                        icon: 1
                    })
                    setTimeout(function() {
                        parent.window.location.reload()
                    }, 1000);
                }
            }, 'json');
        }
    }

    function del(class_id, id, work_id) {
        layer.confirm('确定要删除？', {
            btn: ['确定', '取消'] //按钮
        }, function() {
            $.post('/houtai/work/del', {
                id,
                class_id,
                work_id
            }, function(res) {
                if (res.code > 0) {
                    layer.msg(res.msg, {
                        icon: 2
                    })
                } else {
                    layer.msg(res.msg, {
                        icon: 1
                    })
                    parent.window.location.reload();
                }
            }, 'json')

        });
    }
    // 评论方法
    function detail(class_id, stu_no, work_id) {
        layer.open({
            type: 2,
            title: '作业评价',
            shadeClose: true,
            shade: 0.8,
            area: ['580px', '90%'],
            anim: 5,
            offset: 't',
            content: '/houtai/work/detail?stu_no=' + stu_no + '&work_id=' + work_id + '&class_id=' + class_id,
        });
    }
    </script>
    </script>
</body>

</html>