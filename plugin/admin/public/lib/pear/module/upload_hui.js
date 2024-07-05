layui.define(['layer', 'form', 'jquery', 'upload', 'element'], function (exports) {
  "use strict";
  // 文件上传集合
  var webuploaders = [];
  let $ = layui.$;
  let layer = layui.layer;

  var upload_hui = new function () {
    this.render = function (initConfig) {
      upload_image('.layUpload');
    }
  };

  /**
   * 绑定图片上传组件组件-layuiUpload
   * @param elements  //获取绑定元素
   * @param onUploadSuccess //上传成功
   * @param onUploadError //上传失败
   * 使用案例：
   * <button type="button" class="layui-btn layui-btn-normal layUpload" id="lay_pic" data-multiple="false" data-input-id="lay-c-pic" data-preview-id="lay-p-pic data-type="image"><i class="layui-icon">&#xe67c;</i>上传图片</button>
   */
  function upload_image(elements) {
    elements = typeof elements === 'undefined' ? document.body : elements;
    let chunkSize = typeof GV.site.chunksize !== "undefined" ? GV.site.chunksize : 204800;
    if ($(elements).length > 0) {
      $(elements).each(function () {
        var that = this;
        var id = $(this).prop("id") || $(this).prop("name");
        // 是否多图片上传
        var multiple = $(that).data('multiple');
        var type = $(that).data('type') === 'undefined' ? 'image' : $(that).data('type');
        let Exts = GV.site.upload_image_ext;
        Exts = Exts.replace(/,/g, "|");
        let uploadUrl = GV.upload_url;
        //填充ID
        var input_id = $(that).data("input-id") ? $(that).data("input-id") : "";

        layui.define('upload', function (exports) {
          let upload = layui.upload;
          upload.render({
            elem: "#" + id
            , url: uploadUrl
            , size: chunkSize
            , exts: Exts
            , done: function (res) {
              if (res.code === 200) {
                $("#" + input_id).val(res.url);
                var str = '';
                str += '<dl>';
                str += '<dt><img src="' + res.url + '" data-url="' + res.url + '"></dt>';
                str += '<dd>删除</dd>';
                str += '</dl>';
                $('#' + input_id + '_box').html(str);
              } else {
                layer.msg(res.msg, {icon: 2})
              }
            }, error: function (res, index, upload) {
              console.log(res);
              console.log(index);
              console.log(upload);
            }
          });
        });
      });
    }
  }

  exports('upload_hui', upload_hui);
});
