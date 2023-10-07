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
    <div class="layui-form layui-form-pane">
        <div class="layui-form-item">
            <label class="layui-form-label">作业次数</label>
            <div class="layui-input-block">
                <input type="text" name="work_id" autocomplete="on" placeholder="例：1、2、3" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">截止时间</label>
            <div class="layui-input-block">
                <input type="text" name="work_last_time" autocomplete="off" class="layui-input" id="date">
            </div>
        </div>
        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">作业介绍</label>
            <div class="layui-input-block">
                <textarea placeholder="请输入作业要求" class="layui-textarea" name="work_remarks"></textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">作业状态</label>
            <div class="layui-input-block">
                <input type="checkbox" name="status" lay-skin="switch" lay-text="开启|关闭" checked>
            </div>
        </div>
        <div class="layui-form-item">
            <button class="layui-btn layui-btn-fluid  layui-btn-normal" onclick="save('{$class_id}')">保存</button>
        </div>
    </div>
    <script>
    var laydate = layui.laydate;
    var $ = layui.jquery;
    var form = layui.form;
    var layer = layui.layer;

    //执行一个laydate实例
    laydate.render({
        elem: '#date', //指定元素
        type: 'datetime',
    });


    //保存数据
    function save(class_id) {
        var work_id = $('input[name="work_id"]').val();
        var work_last_time = $('input[name="work_last_time"]').val();
        var work_remarks = $('textarea[name="work_remarks"]').val();
        var status = $('input[name="status"]').is(":checked") ? 0 : 1;

        if (work_id == '' || work_last_time == '' || work_remarks == '') {
            layer.msg('必填项不能为空', {
                icon: 2
            })
        } else {
            $.post('/houtai/work/add', {
                class_id,
                work_id,
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
    </script>
</body>

</html>