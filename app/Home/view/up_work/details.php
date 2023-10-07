<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>作业管理</title>
    <link rel="stylesheet" href="{__LAYUI_CSS_PATH__}">
    <script src="{__LAYUI_JS_PATH__}"></script>
</head>
<style>
.container {
    margin: 60px auto;
    width: 80%;
}
</style>

<body>
    <hr>
    <hr>
    <div style="position: absolute;top:10px;right:10px;">
        {if $is_work.is_true==0}<button
            class="layui-btn layui-btn-normal <?= (($recent_date > $work['work_last_time']) || ($work['status'] == 1)) ? "layui-btn-disabled" : "" ?>"
            onclick="to_do(1,{$work_id},{$stu_no})"
            <?= (($recent_date > $work['work_last_time']) || ($work['status'] == 1)) ? "disabled" : "" ?>>确认提交</button>
        {else /}<button
            class="layui-btn layui-btn-danger <?= (($recent_date > $work['work_last_time']) || ($work['status'] == 1)) ? "layui-btn-disabled" : "" ?>"
            onclick="to_do(0,{$work_id},{$stu_no})"
            <?= (($recent_date > $work['work_last_time']) || ($work['status'] == 1)) ? "disabled" : "" ?>>取消提交</button>
        {/if}
    </div>
    <div class="container">
        <hr>
        <h2>作业详情</h2>
        <p class="layui-elem-field layui-field-title">{$work.work_remarks}</p>
        <hr>
        {if $is_work.is_true==1}
        <p style="padding-bottom:10px">
            <a style="font-size: 30px;text-decoration: underline;"
                href="/storage/{$class_id}/stu_work/{$work_id}/{$stu_no}" target="_blank"><i style="font-size: 30px;"
                    class="layui-icon layui-icon-website"></i><em>访问作业</em></a>
        </p>
        {/if}

        <div class="layui-upload">
            <p>(* 请将文件全部放在同一目录 *)</p>
            <p>(* 请在上传文件时确保有且仅有<em style="color: red;">一个index</em> 文件，默认访问index文件 *)</p>
            <p>(* 暂不支持文件夹上传 *)</p>
            <p>(* 单文件上传限制为3MB *)</p>
            <p>(* 支持文件格式 php、sql、html、css、js、jpg、png、gif、bmp、jpeg、ico、svg、webp、pdf *)</p>
            <hr class="layui-border-red">
            <hr class="layui-border-red">
            {if $is_work.is_true==0}<button type="button"
                class="layui-btn layui-btn-normal <?= (($recent_date > $work['work_last_time']) || ($work['status'] == 1)) ? "layui-btn-disabled" : "" ?>"
                id="testList"
                <?= (($recent_date > $work['work_last_time']) || ($work['status'] == 1)) ? "disabled" : "" ?>>选择多文件</button>
            {else /}<button class="layui-btn layui-btn-normal layui-btn-disabled" disabled>选择多文件</button>
            {/if}

            <div class="layui-upload-list" style="max-width: 1000px;">
                <table class="layui-table">
                    <colgroup>
                        <col>
                        <col width="150">
                        <col width="260">
                        <col width="150">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>文件名</th>
                            <th>大小</th>
                            <th>上传进度</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="demoList"></tbody>
                </table>
            </div>
            {if $is_work.is_true==0}<button type="button"
                class="layui-btn <?= (($recent_date > $work['work_last_time']) || ($work['status'] == 1)) ? "layui-btn-disabled" : "" ?>"
                id="testListAction"
                <?= (($recent_date > $work['work_last_time']) || ($work['status'] == 1)) ? "disabled" : "" ?>>开始上传</button>
            {else /}<button class="layui-btn layui-btn-disabled" disabled>开始上传</button>
            {/if}

        </div>
        <hr class="layui-border-red">
        <hr class="layui-border-red">
        <table class="layui-table">
            <thead>
                <tr>
                    <td>文件名称</td>
                    <td>添加时间</td>
                    <td>操作</td>
                </tr>
            </thead>
            <tbody>
                {volist name='works' id='works'}
                <tr>
                    <td>{$works.filename}</td>
                    <td>{$works.start_time}</td>
                    <td>
                        {if $is_work.is_true==0}<button
                            class="layui-btn layui-btn-danger layui-btn-xs <?= (($recent_date > $work['work_last_time']) || ($work['status'] == 1)) ? "layui-btn-disabled" : "" ?>"
                            onclick="del('{$works.id}')"
                            <?= (($recent_date > $work['work_last_time']) || ($work['status'] == 1)) ? "disabled" : "" ?>>删除</button>
                        {else /}<button class="layui-btn layui-btn-xs layui-btn-disabled" disabled>删除</button>
                        {/if}

                    </td>
                </tr>
                {/volist}
            </tbody>
        </table>

    </div>
    <hr>
    <hr>
    <script>
    var upload = layui.upload; //上传
    var $ = layui.jquery; //上传
    var element = layui.element; //上传
    var layer = layui.layer; //上传

    // 删除方法
    function del(id) {
        layer.confirm('确定要删除？', {
            btn: ['确定', '取消'] //按钮
        }, function() {
            $.post('/www/up_work/delfile', {
                id
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
                    }, 100)
                }
            }, 'json')

        });
    }

    // 确认提交方法
    function to_do(is_true, work_id, stu_no) {

        layer.confirm("确定要" + (is_true == 1 ? "提交" : "取消") + "吗？", {
            btn: ['确定', '取消'] //按钮
        }, function() {
            $.post('/www/up_work/is_true', {
                is_true,
                work_id,
                stu_no
            }, function(res) {
                if (res.code > 0) {
                    layer.msg(res.msg, {
                        icon: 2
                    })
                    setTimeout(function() {
                        window.location.reload();
                    }, 500)
                } else {
                    layer.msg(res.msg, {
                        icon: 1
                    })
                    setTimeout(function() {
                        window.location.reload();
                    }, 500)
                }
            }, 'json')

        });
    }

    //演示多文件列表
    var uploadListIns = upload.render({
        elem: '#testList',
        elemList: $('#demoList') //列表元素对象
            ,
        url: '/www/up_work/upfile' //此处用的是第三方的 http 请求演示，实际使用时改成您自己的上传接口即可。
            ,
        accept: 'file',
        multiple: true,
        auto: false,
        size: 10000, //文件上传尺寸设置
        bindAction: '#testListAction',
        data: {
            stu_no: "{$stu_no}",
            work_id: "{$work_id}"
        },
        choose: function(obj) {
            var that = this;
            var files = this.files = obj.pushFile(); //将每次选择的文件追加到文件队列
            //读取本地文件
            obj.preview(function(index, file, result) {
                var tr = $(['<tr id="upload-' + index + '">', '<td>' + file.name + '</td>', '<td>' +
                    (file.size / 1024).toFixed(1) + 'kb</td>',
                    '<td><div class="layui-progress" lay-filter="progress-demo-' + index +
                    '"><div class="layui-progress-bar" lay-percent=""></div></div></td>',
                    '<td>',
                    '<button class="layui-btn layui-btn-xs demo-reload layui-hide">重传</button>',
                    '<button class="layui-btn layui-btn-xs layui-btn-danger demo-delete">删除</button>',
                    '</td>', '</tr>'
                ].join(''));

                //单个重传
                tr.find('.demo-reload').on('click', function() {
                    obj.upload(index, file);
                });

                //删除
                tr.find('.demo-delete').on('click', function() {
                    delete files[index]; //删除对应的文件
                    tr.remove();
                    uploadListIns.config.elem.next()[0].value =
                        ''; //清空 input file 值，以免删除后出现同名文件不可选
                });

                that.elemList.append(tr);
                element.render('progress'); //渲染新加的进度条组件
            });
        },
        done: function(res, index, upload) { //成功的回调
            var that = this;
            //if(res.code == 0){ //上传成功
            var tr = that.elemList.find('tr#upload-' + index),
                tds = tr.children();
            tds.eq(3).html(''); //清空操作
            delete this.files[index]; //删除文件队列已经上传成功的文件
            if (res.code == 0) {
                layer.msg(res.msg, {
                    icon: 1
                });
            } else {
                layer.msg(res.msg, {
                    icon: 2
                });
                setTimeout(function() {
                    window.location.reload();
                }, '500')
            }

            return;
            //}
            this.error(index, upload);

        },
        allDone: function(obj) { //多文件上传完毕后的状态回调
            setTimeout(function() {
                window.location.reload()
            }, 1000);
        },
        error: function(index, upload) { //错误回调
            var that = this;
            var tr = that.elemList.find('tr#upload-' + index),
                tds = tr.children();
            tds.eq(3).find('.demo-reload').removeClass('layui-hide'); //显示重传
        },
        progress: function(n, elem, e, index) { //注意：index 参数为 layui 2.6.6 新增
            element.progress('progress-demo-' + index, n + '%'); //执行进度条。n 即为返回的进度百分比
        }
    });
    </script>
</body>

</html>