

 <a href="/admin/demo/add"  class="layui-btn layui-btn-big">添加</a>
 <a href="/admin/demo/img"  class="layui-btn layui-btn-big">图片</a>
 <table class="layui-table" lay-data="{ height:600, url:'/admin/demo/data', page:true, id:'idTest', limits:[5,10,15] , limit : 2}" lay-filter="demo">

  <thead>
      <th lay-data="{checkbox:true, fixed: true}"></th>
      <th lay-data="{field:'id', width:80,}">ID</th>
      <th lay-data="{field:'title', width:80}">用户名</th>
      <th lay-data="{field:'sex', width:80,templet: '#sexTpls'}">性别</th>
      <th lay-data="{field:'content', width:80}">城市</th>
      <th lay-data="{fixed: 'right', width:160, align:'center', toolbar: '#barDemo'}">操作</th>
    </tr>
  </thead>
</table>
 <script type="text/html" id="barDemo">
    <a class="layui-btn layui-btn-primary layui-btn-mini" lay-event="detail">查看</a>
    <a class="layui-btn layui-btn-mini" lay-event="edit">编辑</a>
    <a class="layui-btn layui-btn-danger layui-btn-mini" lay-event="del">删除</a>
</script>
<script type="text/html" id="sexTpls">
  {{#  if(d.sex == 1){ }}
    <span style="color: red;">女</span>
  {{#  } else { }}
    男
  {{#  } }}
</script>

<script>
layui.use('table', function(){
  var table = layui.table;
   table.on('tool(demo)', function(obj){
    var data = obj.data;
    alert(obj.event);
    if(obj.event === 'detail'){
      layer.msg('ID：'+ data.id + ' 的查看操作');
    } else if(obj.event === 'del'){
      layer.confirm('真的删除行么', function(index){
        obj.del();
        table.reload('idTest', {
        });
        layer.close(index);
        layer.msg('删除成功', {icon: 1});
       
      });
    } else if(obj.event === 'edit'){
      layer.alert('编辑行：<br>'+ JSON.stringify(data))
    }
  });
});
</script>


</body>
</html>