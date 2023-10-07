<style>
.container {
    margin: 20px auto;

    width: 40%;
    text-align: center;
}
</style>
<div class="container">
    <div class="layui-form layui-form-pane">
        <div class="layui-form-item">
            <label class="layui-form-label">班级代码</label>
            <div class="layui-input-block">
                <input type="text" autocomplete="on" placeholder="请输入全数字编号" class="layui-input"
                    value="{$class.class_id}" disabled>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">课程名称</label>
            <div class="layui-input-block">
                <input type="text" name="class_name" autocomplete="off" placeholder="例：管理信息课程" class="layui-input"
                    value="{$class.class_name}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">课程信息</label>
            <div class="layui-input-block">
                <input type="text" name="class_time" autocomplete="off" placeholder="例：公楼C404 周二上午一二节"
                    class="layui-input" value="{$class.class_time}">
            </div>
        </div>
        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">课程介绍</label>
            <div class="layui-input-block">
                <textarea placeholder="请输入内容课程介绍" class="layui-textarea" name="remarks">{$class.remarks}</textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <button class="layui-btn layui-btn-fluid  layui-btn-normal" onclick="save('{$class.class_id}')">保存</button>
        </div>
    </div>
</div>
<script>
function save(class_id) {
    var class_name = $('input[name="class_name"]').val();
    var class_time = $('input[name="class_time"]').val();
    var remarks = $('textarea[name="remarks"]').val();

    if (class_name == '') {
        layer.msg('必填项不能为空', {
            icon: 2
        })
    } else {
        $.post('/houtai/Classed/save', {
            class_id,
            class_name,
            class_time,
            remarks,
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
                    window.location.reload()
                }, 1000);
            }
        }, 'json');
    }
}
</script>