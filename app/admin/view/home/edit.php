<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>班级添加</title>
</head>
<link rel="stylesheet" href="{__LAYUI_CSS_PATH__}">
<script src="{__LAYUI_JS_PATH__}"></script>
<style>
.container {
    margin: 20px auto;

    width: 40%;
}
</style>

<body>
    <div class="container">
        <input type="hidden" name="id" value="{$class.id}">
        <input type="hidden" name="class_id" value="{$class.class_id}">
        <div class="layui-form layui-form-pane">
            <div class="layui-form-item">
                <label class="layui-form-label">班级代码</label>
                <div class="layui-input-block">
                    <input type="text" autocomplete="on" placeholder="例：2005" class="layui-input"
                        value="{$class.class_id}" disabled>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">课程名称</label>
                <div class="layui-input-block">
                    <input type="text" name="class_name" autocomplete="off" placeholder="例：管理信息课程" class="layui-input"
                        value="{$class.class_name}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">班级状态</label>
                <div class="layui-input-inline">
                    <input type="checkbox" name="status" lay-skin="switch" lay-text="开启|关闭" {$class.status==0? "checked"
                        :""}>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">课程信息</label>
                <div class="layui-input-block">
                    <input type="text" name="class_time" autocomplete="off" placeholder="例：公楼C404 周二上午一二节"
                        class="layui-input" value="{$class.class_time}">
                </div>
            </div>
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">课程介绍</label>
                <div class="layui-input-block">
                    <textarea placeholder="请输入内容课程介绍" class="layui-textarea" name="remarks">{$class.remarks}</textarea>
                </div>
            </div>
            <div class="layui-form-item">
                <button class="layui-btn layui-btn-fluid  layui-btn-normal" onclick="save()">保存</button>
            </div>
        </div>
    </div>
    <script>
    //注意：选项卡 依赖 element 模块，否则无法进行功能性操作
    var element = layui.element;
    var layer = layui.layer;
    var $ = layui.jquery;

    function save() {
        var id = $('input[name="id"]').val();
        var class_id = $('input[name="class_id"]').val();
        var class_name = $('input[name="class_name"]').val();
        var status = $('input[name="status"]').is(":checked") ? 0 : 1;
        var class_time = $('input[name="class_time"]').val();
        var remarks = $('textarea[name="remarks"]').val();

        if (class_name == '') {
            layer.msg('必填项不能为空', {
                icon: 2
            })
        } else {
            $.post('/houtai/home/edit', {
                id,
                class_id,
                class_name,
                status,
                class_time,
                remarks,
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