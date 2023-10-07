<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>学生一键导入</title>
    <link rel="stylesheet" href="{__LAYUI_CSS_PATH__}">
    <script src="{__LAYUI_JS_PATH__}"></script>
</head>

<body style="padding: 10px;">
    <p style="color:red;">为确保文件导入正常，请使用该学生表样表导入：<a href="/example/example.xlsx"
            style="text-decoration: underline;">example.xlsx</a></p>
    <hr>
    <button type="button" class="layui-btn" id="upfile">
        <i class="layui-icon">&#xe67c;</i>导入学生信息
    </button>
    <button type="button" class="layui-btn" id="to_upfile">开始上传</button>
    <script>
    var upload = layui.upload; //得到 upload 对象
    var $ = layui.jquery;
    var layer = layui.layer;

    //创建一个上传组件
    upload.render({
        elem: '#upfile',
        url: '/houtai/stu/upfile',
        data: {
            class_id: '{$class_id}'
        },
        accept: 'file', //允许上传的文件类型
        size: 1000, //最大允许上传的文件大小
        auto: false,
        bindAction: '#to_upfile',
        before: function(obj) { //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
            layer.load(); //上传loading
        },
        done: function(res, index, upload) { //上传后的回调
            if (res.code > 0) {
                layer.msg(res.msg, {
                    icon: 2
                })
                setTimeout(function() {
                    window.location.reload()
                }, 1000);
                layer.closeAll('loading'); //关闭loading

            } else {
                // layer.msg(res.msg);
                layer.msg(res.msg, {
                    icon: 1
                })
                setTimeout(function() {
                    parent.window.location.reload()
                }, 1000);
                layer.closeAll('loading'); //关闭loading
            }
        },
        error: function() {
            layer.msg('上传失败');
            layer.closeAll('loading'); //关闭loading
            //请求异常回调

            setTimeout(function() {
                window.location.reload();
            }, 1000)
        }
    })
    </script>
</body>

</html>