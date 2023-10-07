<!DOCTYPE html>
<html lang="en">

<head>
    <title></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/style.css" rel="stylesheet">
</head>
<link rel="stylesheet" href="{__LAYUI_CSS_PATH__}">
<script src="{__LAYUI_JS_PATH__}"></script>
<style>
.container {
    margin: 20px auto;

    width: 80%;
    text-align: center;
}
</style>

<body>
    <p>建议使用163邮箱</p>
    <p>登录网页版163邮箱，点击设置，开启IMAP/SMTP服务</p>
    <p>扫码发送短信后，获取到授权密码并输入</p>
    <p>SMTP服务器: smtp.163.com</p>
    <div class="container">
        <h1 style="padding: 30px 0;">个人信息维护</h1>

        <div class="layui-form layui-form-pane">
            <div class="layui-form-item">
                <label class="layui-form-label">用户名</label>
                <div class="layui-input-block">
                    <input type="text" autocomplete="on" class="layui-input" value="{$admin.uname}" disabled>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">密码</label>
                <div class="layui-input-block">
                    <input type="text" name="pwd" autocomplete="off" class="layui-input" value="{$admin.pwd}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">邮箱</label>
                <div class="layui-input-block">
                    <input type="email" name="email" autocomplete="off" class="layui-input" value="{$admin.email}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">SMTP服务</label>
                <div class="layui-input-block">
                    <input type="text" name="email_system" autocomplete="off" class="layui-input"
                        value="{$admin.email_system}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">SMTP秘钥</label>
                <div class="layui-input-block">
                    <input type="text" name="check_code" autocomplete="off" class="layui-input"
                        value="{$admin.check_code}">
                </div>
            </div>
            <div class="layui-form-item">
                <button class="layui-btn layui-btn-fluid  layui-btn-normal" onclick="save('{$admin.id}')">保存</button>
            </div>
        </div>
    </div>
    <script>
    var $ = layui.jquery;

    function save(id) {
        var pwd = $('input[name="pwd"]').val();
        var email = $('input[name="email"]').val();

        if (email == '') { //非空校验
            layer.msg('必填项不能为空', {
                icon: 2
            })
        } else { //邮箱格式校验
            if (!email.match(/^\w+@\w+\.\w+$/i)) {
                layer.msg('邮箱格式错误', {
                    icon: 2
                })
            } else {
                $.post('/houtai/home/information', {
                    id,
                    pwd,
                    email,
                }, function(res) {
                    if (res.code > 0) {
                        layer.msg(res.msg, {
                            icon: 2
                        })
                    } else {
                        exit();
                    }
                }, 'json');
            }
        }
    }
    // 退出登录
    function exit() {
        $.post('/houtai/base/exit', {}, function(res) {
            layer.msg('保存成功，请重新登录', {
                icon: 1
            });
            setTimeout(function() {
                parent.window.location.href = "/houtai/login";
            }, 1000);
        }, 'json')
    }
    </script>
</body>

</html>