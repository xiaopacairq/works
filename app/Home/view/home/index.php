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
            <label class="layui-form-label">学号</label>
            <div class="layui-input-block">
                <input type="text" name="class_id" autocomplete="on" class="layui-input" value="{$stu.stu_no}" disabled>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">姓名</label>
            <div class="layui-input-block">
                <input type="text" name="class_id" autocomplete="on" class="layui-input" value="{$stu.stu_name}"
                    disabled>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">性别</label>
            <div class="layui-input-block">
                <input type="text" name="class_id" autocomplete="on" class="layui-input"
                    value='{$stu.gender==0?"男":"女"}' disabled>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">登录时间</label>
            <div class="layui-input-block">
                <input type="text" name="class_id" autocomplete="on" placeholder="例：2005" class="layui-input"
                    value="{$stu.last_time}" disabled>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">课程名称</label>
            <div class="layui-input-block">
                <input type="text" name="class_name" autocomplete="off" placeholder="例：管理信息课程" class="layui-input"
                    value="{$class.class_name}" disabled>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">课程信息</label>
            <div class="layui-input-block">
                <input type="text" name="class_time" autocomplete="off" placeholder="例：公楼C404 周二上午一二节"
                    class="layui-input" value="{$class.class_time}" disabled>
            </div>
        </div>
        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">课程介绍</label>
            <div class="layui-input-block">
                <span style="text-align: start;" placeholder="请输入内容课程介绍" class="layui-textarea"
                    name="remarks">{$class.remarks}</span>
            </div>
        </div>
    </div>
</div>