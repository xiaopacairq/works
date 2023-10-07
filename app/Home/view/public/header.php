<style>
    .header {
        text-align: center;
        margin: 10px auto;
    }

    .layui-nav {
        display: flex;
        justify-content: center;
        align-items: center;

        background-color: white;
    }

    .layui-nav .layui-nav-item a {
        color: #000;
    }

    .layui-nav .layui-nav-item a:hover,
    .layui-nav .layui-this a {
        color: #1E9FFF;
    }

    .layui-nav .layui-this:after,
    .layui-nav-bar {
        background-color: #1E9FFF;
    }

    .exit {
        position: absolute;
        top: 20px;
        right: 10px;

    }
</style>
<button class="layui-btn layui-btn-primary layui-border-black exit" onclick="exit()">退出</button>
<div class="header">
    <h1 style="margin: 20px auto;">{$class.class_id}班作业上传评分系统【hello~{$stu.stu_name}】</h1>
    <ul class="layui-nav" lay-filter="">
        <li class="layui-nav-item {$class.title=='学生主页'? 'layui-this' : ''}"><a href="/www/welcome">学生主页</a></li>
        <li class="layui-nav-item  {$class.title=='作业上传'? 'layui-this' : ''}"><a href="/www/upwork">作业上传</a></li>
        <li class="layui-nav-item {$class.title=='作业展示'? 'layui-this' : ''}""><a href=" /www/diswork">作业展示</a></li>
    </ul>
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
            $.post('/www/base/exit', {}, function(res) {
                layer.msg('退出成功！', {
                    icon: 1
                });
                window.location.href = "/www/login";
            }, 'json')

        });
    }
</script>