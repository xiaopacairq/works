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
            <!-- 添加、清空数据框 -->
            <div class="layui-inline">
                <button
                    class="layui-btn layui-btn-primary layui-border-blue <?= count($work) == 0 ? "layui-btn-disabled" : "" ?>"
                    onclick=" get_zip('{$class.class_id}')" <?= count($work) == 0 ? "disabled" : "" ?>>成绩导出</button>
            </div>
        </div>
    </div>
    <table class="layui-table">
        <colgroup>
            <col width="60">
            <col width="100">
        </colgroup>
        <thead>
            <tr>
                <td>学号</td>
                <td>姓名</td>
                {volist name='work' id='work'}
                <td>作业{$work.work_id}</td>
                {/volist}
                <td>作业成绩</td>
            </tr>
        </thead>
        <tbody>
            {volist name='stu' id='stu'}
            <tr>
                <td>{$stu.stu_no}</td>
                <td>{$stu.stu_name}</td>
                <?php foreach ($stu['work'] as $k => $v) : ?>
                <td><?= $v['score_all'] ?></td>
                <?php endforeach; ?>
                <td><?= number_format($stu['score_alls'] / $work_count, 2) ?></td>
            </tr>
            {/volist}

        </tbody>
    </table>
    <script>
    // 作业导出
    function get_zip(class_id) {
        layer.confirm('确定要下载吗？', {
            btn: ['确定', '取消'] //按钮
        }, function() {
            window.location.href = '/houtai/score/get_zip?class_id=' + class_id;

            setTimeout(function() {
                window.location.reload();
            }, 1000)
        });
    }
    </script>
</div>