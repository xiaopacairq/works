<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>学生添加</title>
    <link rel="stylesheet" href="{__LAYUI_CSS_PATH__}">
    <script src="{__LAYUI_JS_PATH__}"></script>
</head>

<body style="padding:10px">

    <table class="layui-table">
        <thead>
            <tr>
                <td>学号</td>
                <td>姓名</td>
                <td>性别</td>
                <td>邮箱</td>
                <td>登录密码</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><input type="text" name="stu_no" class="layui-input"></td>
                <td><input type="text" name="stu_name" class="layui-input"></td>
                <td>
                    男<input type="radio" name="gender" value="0" title="男" checked>
                    女 <input type="radio" name="gender" value="1" title="女">
                </td>
                <td><input type="email" name="email" class="layui-input"></td>
                <td><input type="text" name="stu_pwd" class="layui-input" placeholder="密码不用填写，随机生成" disabled></td>
            </tr>
        </tbody>
    </table>
    <button class="layui-btn layui-btn-normal" onclick="save('{$class_id}')">保存</button>
    <script>
        var $ = layui.jquery;

        function save(class_id) {
            var stu_no = $('input[name="stu_no"]').val();
            var stu_name = $('input[name="stu_name"]').val();
            var email = $('input[name="email"]').val();
            var gender = $('input[name="gender"]:checked').val();

            if (stu_no == '' || stu_name == '' || email == '') {
                layer.msg('必填项不能为空', {
                    icon: 2
                })
            } else {
                $.post('/houtai/stu/add', {
                    class_id,
                    stu_no,
                    stu_name,
                    email,
                    gender,
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