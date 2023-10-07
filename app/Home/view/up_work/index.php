<style>
    .container {
        margin: 60px auto;

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
    <div class="layui-row layui-col-space15">
        {volist name='work' id='work'}
        <div class="layui-col-md4">
            <div class="layui-panel">
                <div class="panel-head">
                    <h2>作业{$work.work_id}【<?= $work['is_true'] == 1 ? '已提交' : ($work['is_true'] == 0 ? '<span style="color: red;">未提交</span>' : '<span style="color: red;">请确认</span>')   ?>】
                        <?= $work['is_true'] == 1 ? $work['all_score'] . '分' : "" ?>
                    </h2>
                    <hr>
                </div>
                <div class="panel-main" style="height: 80px;">
                    <div class="">
                        介绍：<?= (mb_strlen($work['work_remarks']) > 100) ? mb_substr($work['work_remarks'], 0, 100) . "···" : $work['work_remarks'] ?>

                    </div>
                </div>
                <div class="panel-foot">
                    <em style="color:<?= ($recent_date > $work['work_last_time']) ? "#ccc" : "" ?>">截止时间：{$work.work_last_time|date='Y-m-d
                        H:i'}</em>
                    <button type="button" class="layui-btn layui-btn-sm  layui-btn-warm <?= ($recent_date > $work['work_last_time']) ? "layui-btn-disabled" : "" ?>" onclick='upfile("{$stu.stu_no}","{$work.work_id}")'>
                        <i class="layui-icon">&#xe602;</i>
                    </button>
                </div>
            </div>
        </div>
        {/volist}
    </div>
</div>

<script>
    // 文件上传
    function upfile(stu_no, work_id) {
        layer.open({
            type: 2,
            title: '作业管理',
            maxmin: false, //开启最大化最小化按钮
            area: ['80%', '100%'],
            shadeClose: false,
            anim: 5,
            maxmin: true,
            content: '/www/up_work/details?work_id=' + work_id + '&stu_no=' + stu_no,
            offset: 't',
            shade: 0.3,
            shadeClose: true,
            cancel: function(index, layero) {
                window.location.reload();
            }
        });
    }
</script>