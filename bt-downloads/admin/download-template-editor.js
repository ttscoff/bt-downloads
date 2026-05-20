(function () {
  "use strict";

  var templateTextarea = document.getElementById("btdl_template");
  var stylePresetSelect = document.getElementById("btdl_style_preset");
  var iframe = document.getElementById("btdl_css_preview");
  var themeIframe = document.getElementById("btdl_theme_preview");
  var themeReloadBtn = document.getElementById("btdl_preview_theme_reload");
  var timeout;

  var config = window.btdlTemplateEditor || {};
  var previewUrl = config.ajaxUrl || "";
  var nonce = config.nonce || "";

  function buildPreview() {
    if (!iframe || !previewUrl || !nonce) return;
    var template = templateTextarea ? templateTextarea.value : "";
    var stylePreset = stylePresetSelect ? stylePresetSelect.value : "";
    var formData = new FormData();
    formData.append("action", "btdl_card_preview");
    formData.append("nonce", nonce);
    if (template) formData.append("btdl_template", template);
    if (stylePreset) formData.append("btdl_style_preset", stylePreset);
    fetch(previewUrl, { method: "POST", body: formData, credentials: "same-origin" })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (data.success && data.data && data.data.html) {
          iframe.srcdoc = data.data.html;
        }
      })
      .catch(function () {
        iframe.srcdoc = "";
      });
  }

  function debouncedPreview() {
    clearTimeout(timeout);
    timeout = setTimeout(buildPreview, 300);
  }

  buildPreview();

  if (templateTextarea) {
    templateTextarea.addEventListener("input", debouncedPreview);
    templateTextarea.addEventListener("change", buildPreview);
  }
  if (stylePresetSelect) {
    stylePresetSelect.addEventListener("change", buildPreview);
  }
  if (themeReloadBtn && themeIframe) {
    themeReloadBtn.addEventListener("click", function () {
      themeIframe.src = themeIframe.src;
    });
  }
})();
