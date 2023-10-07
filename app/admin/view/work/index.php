<style>
    .container {
        margin: 20px auto;

        width: 60%;
        text-align: center;
    }

    .layui-panel {
        padding: 10px;

    }

    .panel-head {
        text-align: start;

    }

    .panel-main {
        margin: 20px 0;
        text-align: start;
    }

    .panel-foot {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
<div class="container">
    <div class="layui-form">
        <div class="layui-form-item">
            <!-- 添加、清空数据框 -->
            <div class="layui-inline">
                <button class="layui-btn layui-btn-primary layui-border-black" onclick="add('{$class.class_id}')">布置作业</button>
                <button class="layui-btn layui-btn-primary layui-border-blue <?= count($work) == 0 ? "layui-btn-disabled" : "" ?>" onclick=" get_zip('{$class.class_id}')" <?= count($work) == 0 ? "disabled" : "" ?>>作业导出</button>
            </div>
        </div>
    </div>
    <div class="layui-row layui-col-space15">
        {volist name='work' id='work'}
        <div class="layui-col-md4">
            <div class="layui-panel" style="color:<?= (($recent_date > $work['work_last_time']) || ($work['status'] == 1)) ? "#ccc" : "" ?> ;">
                <div class="panel-head">
                    <h2>作业{$work.work_id}</h2>
                    <hr>
                </div>
                <div class="panel-main" style="height: 80px;">
                    <div class="">
                        介绍：<?= (mb_strlen($work['work_remarks']) > 100) ? mb_substr($work['work_remarks'], 0, 100) . "···" : $work['work_remarks'] ?>

                    </div>
                </div>
                <div class="panel-foot">
                    <i class="">截止时间：{$work.work_last_time|date='Y-m-d H:i'}</i>
                    <button type="button" class="layui-btn layui-btn-sm  layui-btn-warm" onclick='edit("{$class.class_id}","{$work.id}","{$work.work_id}")'>
                        <i class="layui-icon">&#xe602;</i>
                    </button>
                </div>
            </div>
        </div>
        {/volist}
    </div>
</div>

<script>
    // 预定义变量

    // 作业导出
    function get_zip(class_id) {
        layer.confirm('确定要下载吗？', {
            btn: ['确定', '取消'] //按钮
        }, function() {
            layer.msg('正在导出作业，请勿重复点击！', {
                icon: 1,
                time: 2000
            });
            window.location.href = '/houtai/work/get_zip?class_id=' + class_id;
        });
    }

    // 添加
    function add(class_id) {
        layer.open({
            type: 2,
            title: '作业布置',
            maxmin: false, //开启最大化最小化按钮
            area: ['80%', '100%'],
            shadeClose: false,
            anim: 5,
            content: '/houtai/work/add?class_id=' + class_id,
            offset: 't',
            shade: 0.3,
            shadeClose: true
        });
    }
    // 修改
    function edit(class_id, id, work_id) {
        layer.open({
            type: 2,
            title: '作业管理',
            maxmin: false, //开启最大化最小化按钮
            area: ['80%', '100%'],
            shadeClose: false,
            anim: 5,
            content: '/houtai/work/edit?class_id=' + class_id + '&id=' + id + ' &work_id= ' + work_id,
            offset: 't',
            shade: 0.3,
            maxmin: true,
            shadeClose: true
        });
    }
</script>