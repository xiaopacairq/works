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
        <table class="layui-table">
            <thead>
                <tr>
                    <th>学号</th>
                    <th>评分</th>
                </tr>
            </thead>
            <tbody>
                {volist name='score' id='score'}
                <tr>
                    <td>{$score.to_stu_no}:</td>
                    <td id="{$score.id}">{$score.score / 10}</td>
                </tr>
                <script>
                var rate = layui.rate;
                rate.render({
                    elem: "#{$score.id}",
                    value: "{$score.score}" / 10,
                    text: true,
                    length: 10,
                    readonly: true
                })
                </script>
                {/volist}
            </tbody>
        </table>
    </div>

</body>

</html>