<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>作业上传评分系统学生门户</title>
    <!-- 引入login.css样式文件 -->
    <link rel="stylesheet" href="/static/css/login.css">
    <!-- 引入layui组件库 -->
    <link rel="stylesheet" href="/static/layui/css/layui.css">
    <script src="/static/layui/layui.js"></script>
    <!-- 引入vantajs界面库 -->
    <script src="/static/js/three.r134.min.js"></script>
    <script src="/static/js/vanta.birds.min.js"></script>
    <!-- 设置网站图标 -->
    <link rel="shortcut icon" href="/static/images/index.ico" type="image/x-icon">
</head>

<body>
    <div class="head"></div>
    <div class="layui-container">
        <div class="head-title">
            <img class="logo" src="/static/images/index.svg" alt="">
            <span class="title">作业上传评分系统学生门户</span>
        </div>
        <div class="layui-from">
            <div class="form-item">
                <div class="label"><i class="layui-icon layui-icon-location"></i></div>
                <div class="input-item">
                    <input type="text" name="class_id" required lay-verify="required" placeholder="请输入班级代码"
                        autocomplete="off" class="layui-input" value="">
                </div>
            </div>
            <div class="form-item">
                <div class="label"><i class="layui-icon layui-icon-username"></i></div>
                <div class="input-item">
                    <input type="text" name="stu_no" required lay-verify="required" placeholder="请输入学号"
                        autocomplete="off" class="layui-input" value="">
                </div>
            </div>
            <div class="form-item">
                <div class="label"><i class="layui-icon layui-icon-password"></i></div>
                <div class="input-item">
                    <input type="password" name="stu_pwd" required lay-verify="required" placeholder="请输入密码"
                        autocomplete="off" class="layui-input" value="">
                </div>
            </div>
            <div class="form-item">
                <div class="label"><i class="layui-icon layui-icon-vercode"></i></div>
                <div class="input-item captcha">
                    <input type="text" name="captcha" required lay-verify="required" placeholder="请输入验证码"
                        autocomplete="off" class="layui-input">
                    <img id="captcha" src="/www/account/verify" alt="" onclick="reloadveriimg(this)">
                </div>
            </div>
            <div class="form-item">
                <button class="layui-btn layui-btn-normal" onclick="do_login()">登录</button>
            </div>
        </div>
    </div>

    </div>
    <script>
    VANTA.BIRDS({
        el: ".head",
        mouseControls: true,
        touchControls: true,
        gyroControls: false,
        minHeight: 200.00,
        minWidth: 200.00,
        scale: 1.00,
        scaleMobile: 1.00
    })

    $ = layui.jquery;

    // 刷新验证码
    function reloadveriimg(obj) {
        // 加入随机字符， 表示验证码图片已经发生改变
        $(obj).attr('src', '/www/account/verify?rand=' + Math.random());
    }

    // 登录校验
    function do_login() {
        var class_id = $('input[name="class_id"]').val();
        var stu_no = $('input[name="stu_no"]').val();
        var stu_pwd = $('input[name="stu_pwd"]').val();
        var captcha = $('input[name="captcha"]').val();

        if (class_id == '' || stu_no == '' || stu_pwd == '' || captcha == '') {
            layer.msg('必填项不能为空', {
                icon: 2
            })
        } else {
            $.post('/www/account/do_login', {
                class_id,
                stu_no,
                stu_pwd,
                captcha
            }, function(res) {
                console.log(res)
                if (res.code > 0) {
                    layer.msg(res.msg, {
                        icon: 2
                    })
                    reloadveriimg($('#captcha'));
                } else {
                    layer.msg(res.msg, {
                        icon: 1
                    })
                    setTimeout(function() {
                        window.location.href = '/www/welcome'
                    }, 1000);
                }
            }, 'json');
        }
    }
    </script>
</body>

</html>