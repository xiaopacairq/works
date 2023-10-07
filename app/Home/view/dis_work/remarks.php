<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>作业评价</title>
    <link rel="stylesheet" href="{__LAYUI_CSS_PATH__}">
    <script src="{__LAYUI_JS_PATH__}"></script>
</head>
<style>
.container {
    margin: 0.01px auto;
}

.box {
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>

<body>
    <div class="container">
        <div class="box">
            <span style="padding: 10px;">我的评分</span>
            <div id="test6"></div>
        </div>
        <hr>
        <table class="layui-table">
            <thead>
                <tr>
                    <th>学号</th>
                    <th>评分</th>
                </tr>
            </thead>
            <tbody>
                {volist name='remarks' id='remarks'}
                <tr>
                    <td>{$remarks.to_stu_no}:</td>
                    <td id="{$remarks.id}">{$remarks.score / 20}</td>
                </tr>
                <script>
                var rate = layui.rate;
                rate.render({
                    elem: "#{$remarks.id}",
                    value: "{$remarks.score}" / 10,
                    length: 10,
                    readonly: true
                })
                </script>
                {/volist}
            </tbody>
        </table>
    </div>

    <script>
    var $ = layui.jquery; //上传
    var rate = layui.rate;
    var layer = layui.layer;

    rate.render({
        elem: '#test6',
        value: "{$score}" / 10,
        text: true,
        length: 10,
        readonly: "{$is_true}",
        setText: function(value) {
            this.span.text(value);
        },
        choose: function(value) {
            layer.confirm('评分后不能修改！', {
                btn: ['确定', '取消'] //按钮
            }, function() {
                $.post('/www/dis_work/remarks', {
                    score: value * 10,
                    work_id: "{$work_id}",
                    stu_no: "{$stu_no}",
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
                        }, 500)
                    }
                }, 'json');
            }, function() {
                layer.msg('你已取消评分');
                setTimeout(function() {
                    window.location.reload();
                }, 500)
            });


        }
    })
    </script>
</body>

</html>