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
                    <h2>作业{$work.work_id}【{$work.is_true_stu_num}/{$work.stu_num}】</h2>
                    <hr>
                </div>
                <div class="panel-main" style="height: 80px;">
                    <div class="">
                        介绍：<?= (mb_strlen($work['work_remarks']) > 100) ? mb_substr($work['work_remarks'], 0, 100) . "···" : $work['work_remarks'] ?>

                    </div>
                </div>
                <div class="panel-foot">
                    <i class="">截止时间：{$work.work_last_time|date='Y-m-d H:i'}</i>
                    <button type="button" class="layui-btn layui-btn-sm layui-btn-normal" onclick='to_remarks("{$work.work_id}")'>
                        <i class="layui-icon">&#xe66e;</i>
                    </button>
                </div>
            </div>
        </div>
        {/volist}
    </div>
</div>

<script>
    // 评论
    function to_remarks(work_id) {
        layer.open({
            type: 2,
            title: '作业管理',
            maxmin: false, //开启最大化最小化按钮
            area: ['80%', '100%'],
            shadeClose: false,
            anim: 5,
            content: '/www/dis_work/display?work_id=' + work_id,
            offset: 't',
            shade: 0.3,
            shadeClose: true
        });
    }
</script>