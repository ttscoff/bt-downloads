/**
 * TinyMCE plugin: "Insert download" button. Opens the picker modal.
 */
(function () {
  "use strict";

  function register() {
    if (typeof tinymce === "undefined") return;
    tinymce.PluginManager.add("btdl_insert_download", function (editor) {
      editor.addButton("btdl_insert_download", {
        title: "Insert download",
        icon: "btdl-download",
        onclick: function () {
          if (window.btdlDownloadPickerOpen) {
            window.btdlDownloadPickerOpen(function (shortcode) {
              editor.insertContent(shortcode);
            });
          }
        }
      });
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", register);
  } else {
    register();
  }
})();
