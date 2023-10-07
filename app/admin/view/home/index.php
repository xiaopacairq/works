<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>班级门户</title>
</head>
<link rel="stylesheet" href="{__LAYUI_CSS_PATH__}">
<script src="{__LAYUI_JS_PATH__}"></script>
<style>
.header {
    text-align: center;
    margin: 10px auto;
}

.exit {
    position: absolute;
    top: 20px;
    right: 10px;
}

.container {
    margin: 60px auto;

    width: 60%;
}


.footer {
    position: relative;
    bottom: 10px;
    width: 100%;
    text-align: center;
}
</style>

<body>
    <button class="layui-btn layui-btn-primary layui-border-black exit" onclick="exit()">退出</button>
    <div class="header">
        <h1 style="margin: 20px auto;">班级管理门户【{$admin.uname}】</h1>
    </div>
    <div class="container">
        <div class="layui-form">
            <div class="layui-form-item">
                <!-- 添加、清空数据框 -->
                <div class="layui-inline">
                    <button class="layui-btn layui-btn-primary layui-border-black" onclick="add()">添加班级</button>
                    <button class="layui-btn layui-btn-primary layui-border-black" onclick="information()">个人信息</button>
                </div>
            </div>
        </div>
        <h5><em><span style="color:red">注意：</span></em></h5>
        <p><em>个人信息的<span style="color:red">邮箱</span>是发送短信的邮箱<span style="color:red">请自行修改</span></em></p>
        <p><em>班级代码只支持<span style="color:red">数字</span>命名，班级一旦生成，班级代码将<span style="color:red">无法修改</span></em></p>
        <p><em>可以设置<span style="color:red">班级状态：</span></em></p>
        <p><em>①正常表示课程正在进行！</em></p>
        <p><em>②结课学生将无法进入系统！</em></p>
        <p><em><span style="color:red">下载文件</span>包括学生的名单、成绩、作业!</em></p>
        <table class="layui-table">
            <thead>
                <tr>
                    <th>班级代码</th>
                    <th>课程名称</th>
                    <th>班级创建时间</th>
                    <th>班级状态</th>
                    <th>访问班级</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                {volist name='classes' id='classes'}
                <tr>
                    <td>{$classes.class_id}</td>
                    <td>{$classes.class_name}</td>
                    <td>{$classes.start_time}</td>
                    <td><?= $classes['status'] == 0 ? "正常" : "<span style=color:red>关闭</span>" ?></td>
                    <td>
                        <a href="/houtai/class_wz?class_id={$classes.class_id}">
                            <button class="layui-btn layui-btn-normal layui-btn-xs">进入班级</button>
                        </a>
                    </td>
                    <td>
                        <button class="layui-btn  layui-btn-xs" onclick="get_zip('{$classes.class_id}')">下载</button>
                        <button class="layui-btn layui-btn-warm layui-btn-xs"
                            onclick="edit('{$classes.id}')">修改</button>
                        <button class="layui-btn layui-btn-danger layui-btn-xs"
                            onclick="del('{$classes.id}','{$classes.class_id}')">删除</button>

                    </td>
                </tr>
                {/volist}
            </tbody>

        </table>
    </div>
    <div class="footer">
        <p>ThinkPHP</p>
        <p>以及勤劳的自己</p>
    </div>
    <script>
    //注意：选项卡 依赖 element 模块，否则无法进行功能性操作
    var element = layui.element;
    var layer = layui.layer;
    var $ = layui.jquery;


    // 退出登录
    function exit() {
        layer.confirm('是否退出登录', {
            btn: ['确定', '取消'] //按钮
        }, function() {
            $.post('/houtai/base/exit', {}, function(res) {
                layer.msg('退出成功！', {
                    icon: 1
                });
                setTimeout(function() {
                    window.location.href = "/houtai/login";
                }, 1000)
            }, 'json')

        });
    }

    // 个人信息
    function information() {
        layer.open({
            type: 2,
            title: '个人信息管理',
            maxmin: false, //开启最大化最小化按钮
            area: ['60%', '90%'],
            shadeClose: false,
            anim: 5,
            content: '/houtai/home/information',
            offset: 't',
            shade: 0.3,
            maxmin: true,
            shadeClose: true
        });
    }

    // 添加
    function add() {
        layer.open({
            type: 2,
            title: '班级添加',
            maxmin: false, //开启最大化最小化按钮
            area: ['80%', '500px'],
            shadeClose: false,
            anim: 5,
            content: '/houtai/home/add',
            offset: 't',
            shade: 0.3,
            shadeClose: true
        });
    }
    // 修改
    function edit(id) {
        layer.open({
            type: 2,
            title: '班级修改',
            maxmin: false, //开启最大化最小化按钮
            area: ['80%', '100%'],
            shadeClose: false,
            anim: 5,
            content: '/houtai/home/edit?id=' + id,
            offset: 't',
            shade: 0.3,
            maxmin: true,
            shadeClose: true
        });
    }

    // 删除
    function del(id, class_id) {
        layer.confirm('请在下载备份数据后删除', {
            btn: ['下载', '确定', '取消'] //按钮
        }, function() {
            window.location.href = '/houtai/home/get_zip?class_id=' + class_id;
        }, function() {
            $.post('/houtai/home/del', {
                id,
                class_id
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
                        parent.window.location.reload();
                    }, 1000)
                }
            }, 'json')

        });
    }

    function get_zip(class_id) {
        layer.confirm('确定要下载吗？', {
            btn: ['确定', '取消'] //按钮
        }, function() {
            layer.msg('正在导出作业，请勿重复点击！', {
                icon: 1,
                time: 2000
            });
            window.location.href = '/houtai/home/get_zip?class_id=' + class_id;
        });
    }
    </script>
</body>

</html>