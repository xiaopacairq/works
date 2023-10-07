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

.exit2 {
    position: absolute;
    top: 20px;
    right: 100px;

}
</style>
<button class="layui-btn layui-btn-primary layui-border-black exit2"
    onclick="window.location.href='/houtai/welcome'">返回</button>
<button class="layui-btn layui-btn-primary layui-border-black exit" onclick="exit()">退出</button>
<div class="header">
    <h1 style="margin: 20px auto;">{$class.class_id}班作业管理后台【{$admin.uname}】</h1>
    <ul class="layui-nav" lay-filter="">
        <li class="layui-nav-item {$class.title=='班级配置'? 'layui-this' : ''}"><a
                href="/houtai/class_wz?class_id={$class.class_id}">班级配置</a></li>
        <li class="layui-nav-item  {$class.title=='学生管理'? 'layui-this' : ''}"><a
                href="/houtai/student?class_id={$class.class_id}">学生管理</a></li>
        <li class="layui-nav-item {$class.title=='作业布置'? 'layui-this' : ''}""><a href="
            /houtai/works?class_id={$class.class_id}">作业布置</a></li>
        <li class=" layui-nav-item {$class.title=='成绩管理' ? 'layui-this' : '' }""><a href="
            /houtai/scores?class_id={$class.class_id}">成绩管理</a></li>
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
        $.post('/houtai/base/exit', {}, function(res) {
            layer.msg('退出成功！', {
                icon: 1
            });
            setTimeout(function() {
                parent.window.location.href = "/houtai/login";
            }, 1000)
        }, 'json')

    });
}
</script>