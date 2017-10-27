layui.use('upload', function(){
  var $ = layui.jquery,
  upload = layui.upload;
  var duotu = true;
  upload.render({
     elem: '#test2',
     url: '/admin/upload/imgs',
     multiple: duotu,
     data: {'_csrf':'TlhROE5NRVB8HwdpFCIUfXsqKV0FAyoHHQkaaSEBHCAlNzUIOS4EPw=='},
     done: function(res){
      var imgName = $('.layui-imgs').attr('nametext')
      $('.layui-upload-lists').append('<span class="item_img" id="' + res.imgid + '"><span class="operate"><img class="cl" src="/statics/themes/admin/images/c.png" alt="" onclick=UPLOAD_IMG_DEL("' + res.imgid + '")></span><img src="/' + res.url + '" class="img" ><input type="hidden" name="'+imgName+'" value="' + res.url + '" /></span>');
         
     }
  });//多长
  upload.render({
    elem: '#test1',
    url: '/admin/upload/imgs',
    data: {'_csrf':'TlhROE5NRVB8HwdpFCIUfXsqKV0FAyoHHQkaaSEBHCAlNzUIOS4EPw=='},
    done: function(res){
      var imgName = $('.layui-img').attr('nametext')
      $('.layui-upload-list').html('<span class="item_img" id="' + res.imgid + '"><span class="operate"><img class="cl" src="/statics/themes/admin/images/c.png" alt="" onclick=UPLOAD_IMG_DEL("' + res.imgid + '")></span><img src="/' + res.url + '" class="img" ><input type="hidden" name="'+imgName+'" value="' + res.url + '" /></span>')
    }
   
  });//单张
});
function UPLOAD_IMG_DEL(divs) {
  $("#"+divs).remove();
}