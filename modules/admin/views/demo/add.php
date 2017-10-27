	   <?php
	        use yii\bootstrap\ActiveForm;
	        use yii\helpers\Html;
       ?>
 <link href="/statics/themes/admin/css/upload.css" rel="stylesheet">
         <?php $form = ActiveForm::begin(['options' => ['class' => 'layui-form layui-form-pane']]);?>
		               
		    <div class="layui-form-item">
			    <label class="layui-form-label">验证必填项</label>
			    <div class="layui-input-block">
			      <input type="text" name="username" lay-verify="required" placeholder="请输入" autocomplete="off" class="layui-input">
			    </div>
			</div>
	        <div class="layui-form-item" pane="">
	            <label class="layui-form-label">单选框</label>
			    <div class="layui-input-block">
			      <input type="radio" name="sex" value="2" title="男" checked="">
			      <input type="radio" name="sex" value="1" title="女">
				    </div>
		    </div>
			<div class="layui-form-item layui-form-text">
			    <label class="layui-form-label">文本域</label>
			    <div class="layui-input-block">
			      <textarea placeholder="请输入内容" name="content" class="layui-textarea"></textarea>
			    </div>
           <?php echo $form->field($model, 'img')->textInput();?>
			</div>

      <div class="layui-upload">
        <button type="button" class="layui-btn" id="test1">图片上传</button> 
          <input type="hidden" nametext="Demo['img']" class="layui-img">
        <div class="layui-upload-list">
           <!-- 展示图片的地方 -->
        </div>
      </div>   
 
			<div class="layui-form-item">
			    <button class="layui-btn" lay-submit="" lay-filter="demo2">提交</button>
			</div>
		 <?php ActiveForm::end(); ?>

<script src="/statics/themes/admin/js/upload.js"></script>

<script>
layui.use(['form', 'layedit', 'laydate'], function(){
  var form = layui.form,
  layer = layui.layer,
  layedit = layui.layedit,
  laydate = layui.laydate;
  laydate.render({
    elem: '#date'
  });
  laydate.render({
    elem: '#date1'
  });
  form.verify({
    title: function(value){
      if(!value){
        return '标题不能为空';
      }
    }
  });
  $('.layui-btn').click(function(){
  	  var data = $("#w0").serializeArray();
  	  var url = '/admin/demo/add';
      postData = {};
      $(data).each(function(i){
        postData[this.name] = this.value;
      });

      $.post(url,postData,function(result){
       
      },"JSON");
     return false;
  })
  
});
</script>