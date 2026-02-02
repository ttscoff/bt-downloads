/**
 * Download edit screen: upload/select file for File URL and Icon URL.
 * Uses wp.media; passes post_id so uploads go to uploads/downloads/.
 */
(function ($) {
  "use strict";

  var postId = parseInt($("#post_ID").val(), 10) || 0;

  function openMediaFrame(targetInputId, options) {
    options = options || {};
    var isIcon = targetInputId === "btdl_download_icon";
    var frame = wp.media({
      title:
        options.title ||
        (isIcon ? "Select or upload image" : "Select or upload file"),
      library: options.library || (isIcon ? { type: "image" } : {}),
      button: { text: options.buttonText || "Use this file" },
      multiple: false
    });

    frame.on("open", function () {
      if (postId && frame.uploader && frame.uploader.uploader) {
        frame.uploader.uploader.settings.multipart_params =
          frame.uploader.uploader.settings.multipart_params || {};
        frame.uploader.uploader.settings.multipart_params.post_id = postId;
      }
    });

    frame.on("select", function () {
      var attachment = frame.state().get("selection").first().toJSON();
      var url = attachment.url;
      if (url) {
        $("#" + targetInputId).val(url);
      }
    });

    frame.open();
  }

  function setNowForDatetimeField(inputId) {
    var el = document.getElementById(inputId);
    if (!el || el.type !== "datetime-local") return;
    var d = new Date();
    var pad = function (n) {
      return n < 10 ? "0" + n : String(n);
    };
    var value =
      d.getFullYear() +
      "-" +
      pad(d.getMonth() + 1) +
      "-" +
      pad(d.getDate()) +
      "T" +
      pad(d.getHours()) +
      ":" +
      pad(d.getMinutes());
    el.value = value;
  }

  $(function () {
    $(document).on("click", ".btdl-upload-file", function () {
      var target = $(this).data("target");
      if (!target) return;
      var isIcon = target === "btdl_download_icon";
      openMediaFrame(target, {
        library: isIcon ? { type: "image" } : {},
        title: isIcon
          ? "Select or upload icon image"
          : "Select or upload download file"
      });
    });

    $(document).on("click", ".btdl-set-now", function (e) {
      e.preventDefault();
      var target = $(this).data("target");
      if (target) setNowForDatetimeField(target);
    });
  });
})(jQuery);
