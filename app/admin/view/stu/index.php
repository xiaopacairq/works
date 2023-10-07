<style>
.container {
    margin: 20px auto;

    width: 90%;
    text-align: center;
}
</style>
<div class="container">
    <div class="layui-form">
        <div class="layui-form-item">
            <!-- 搜索学生 -->
            <div class="layui-inline">
                <input type="text" name="search" placeholder="搜索学生" autocomplete="off" class="layui-input"
                    value="{$search?$search:''}">
            </div>
            <!-- 搜索按钮 -->
            <div class="layui-inline">
                <button class="layui-btn layui-btn-normal" onclick="search('{$class.class_id}')"><i
                        class="layui-icon layui-icon-search" style="font-size:24px"></i></button>
            </div>
            <!-- 添加、清空数据框 -->
            <div class="layui-inline">
                <button
                    class="layui-btn layui-btn-primary layui-border-black <?= count($stu) == 0 ? "layui-btn-disabled" : "" ?>"
                    onclick="add('{$class.class_id}')" <?= count($stu) == 0 ? "disabled" : "" ?>>添加</button>
                <button
                    class="layui-btn layui-btn-primary layui-border-black <?= count($stu) != 0 ? "layui-btn-disabled" : "" ?>"
                    onclick="up_stu('{$class.class_id}')" id="upfile"
                    <?= count($stu) != 0 ? "disabled" : "" ?>>一键导入</button>
                <button
                    class="layui-btn layui-btn-primary layui-border-black <?= count($stu) == 0 ? "layui-btn-disabled" : "" ?>"
                    onclick=" clear_all('{$class.class_id}')" <?= count($stu) == 0 ? "disabled" : "" ?>>一键清空</button>
                <button
                    class="layui-btn layui-btn-primary layui-border-black <?= count($stu) == 0 ? "layui-btn-disabled" : "" ?>"
                    onclick=" send_all_email('{$class.class_id}','{$class.class_name}',is_all=1)"
                    <?= count($stu) == 0 ? "disabled" : "" ?>>密码群发</button>
                <button
                    class="layui-btn layui-btn-primary layui-border-blue <?= count($stu) == 0 ? "layui-btn-disabled" : "" ?>"
                    onclick=" get_zip('{$class.class_id}')" <?= count($stu) == 0 ? "disabled" : "" ?>>学生名单导出</button>
            </div>
        </div>
    </div>
    <table class=" layui-table">
        <thead>
            <tr>
                <td>班级</td>
                <td>学号</td>
                <td>姓名</td>
                <td>性别</td>
                <td>邮箱</td>
                <td>登录密码</td>
                <td>最近登录时间</td>
                <td>操作</td>
            </tr>
        </thead>
        <tbody>
            {volist name='stu' id='stu'}
            <tr>
                <td>{$stu.class_id}</td>
                <td>{$stu.stu_no}</td>
                <td>{$stu.stu_name}</td>
                <td>{$stu.gender==0 ? "男":"女"}</td>
                <td>{$stu.email}</td>
                <td>{$stu.stu_pwd}</td>
                <td>{$stu.last_time}</td>
                <td>
                    <button class="layui-btn  layui-btn-xs"
                        onclick="send_email('{$class.class_id}','{$class.class_name}','{$stu.stu_no}')">密码分发</button>
                    <button class="layui-btn layui-btn-warm layui-btn-xs"
                        onclick="edit('{$class.class_id}','{$stu.stu_no}')">修改</button>
                    <button class="layui-btn layui-btn-danger layui-btn-xs"
                        onclick="del('{$class.class_id}','{$stu.stu_no}')">删除</button>
                </td>
            </tr>
            {/volist}
        </tbody>
    </table>
    {$page|raw}
</div>

<script>
var upload = layui.upload; //上传文件功能
// 预定义变量

// 学生名单导出
function get_zip(class_id) {
    layer.confirm('确定要下载吗？', {
        btn: ['确定', '取消'] //按钮
    }, function() {
        window.location.href = '/houtai/stu/get_zip?class_id=' + class_id;

        setTimeout(function() {
            window.location.reload();
        }, 1000)
    });
}

// 添加  
function add(class_id) {
    layer.open({
        type: 2,
        title: '学生添加',
        maxmin: false, //开启最大化最小化按钮
        area: ['80%', '250px'],
        shadeClose: false,
        anim: 5,
        content: '/houtai/stu/add?class_id=' + class_id,
        offset: 't',
        shade: 0.3,
        shadeClose: true
    });
}

// 学生一键导入页面
function up_stu(class_id) {
    layer.open({
        type: 2,
        title: '学生一键导入',
        skin: 'layui-layer-rim', //加上边框
        area: ['520px', '240px'], //宽高
        shade: 0.8,
        content: '/houtai/stu/up_stu?class_id=' + class_id
    });
}

//学生密码群发
function send_all_email(class_id, class_name, is_all) {
    layer.confirm('确定要群发吗？', {
        btn: ['确定', '取消'] //按钮
    }, function() {
        var index = layer.load(1); //换了种风格
        $.post('/houtai/stu/send_email', {
            class_id,
            class_name,
            is_all
        }, function(res) {
            if (res.code > 0) {
                layer.msg(res.msg, {
                    icon: 2
                })
                layer.close(index);
            } else {
                layer.msg(res.msg, {
                    icon: 1
                })
                layer.close(index);
                setTimeout(function() {
                    window.location.reload();
                }, 1000)
            }
        }, 'json')

    });
}
//学生密码单发
function send_email(class_id, class_name, stu_no) {
    layer.confirm('确定要发送吗？', {
        btn: ['确定', '取消'] //按钮
    }, function() {
        $.post('/houtai/stu/send_email', {
            class_id,
            class_name,
            stu_no
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
                    window.location.reload();
                }, 1000)

            }
        }, 'json')

    });
}

// 修改
function edit(class_id, stu_no) {
    layer.open({
        type: 2,
        title: '学生修改',
        maxmin: false, //开启最大化最小化按钮
        area: ['80%', '250px'],
        shadeClose: false,
        anim: 5,
        content: '/houtai/stu/edit?class_id=' + class_id + '&stu_no=' + stu_no,
        offset: 't',
        shade: 0.3,
        shadeClose: true
    });
}
// 删除
function del(class_id, stu_no) {
    layer.confirm('确定要删除？', {
        btn: ['确定', '取消'] //按钮
    }, function() {
        $.post('/houtai/stu/del', {
            class_id,
            stu_no
        }, function(res) {
            if (res.code > 0) {
                layer.msg(res.msg, {
                    icon: 2
                })
            } else {
                layer.msg(res.msg, {
                    icon: 1
                })
                window.location.reload();
            }
        }, 'json')

    });
}
// 搜索
function search(class_id) {
    var search = $.trim($('input[name="search"]').val()); //搜索内容
    window.location.href = '/houtai/student?class_id=' + class_id + '&search=' + search;
}
// 清空
function clear_all(class_id) {
    layer.confirm('确定要清空学生信息吗？', {
        btn: ['确定', '取消'] //按钮
    }, function() {
        $.post('/houtai/stu/del', {
            class_id,
            is_clear_all: 1 //传递该值表示清空数据表
        }, function(res) {
            if (res.code > 0) {
                layer.msg(res.msg, {
                    icon: 2
                })
            } else {
                layer.msg(res.msg, {
                    icon: 1
                })
                window.location.reload();
            }
        }, 'json')
    });
}
</script>