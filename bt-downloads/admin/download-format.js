/**
 * Block editor format: Insert download. Adds "Download" to the format toolbar dropdown.
 * When clicked, opens the picker modal; on select, inserts [download ID] at cursor.
 */
(function (richText, blockEditor, element, i18n) {
  "use strict";

  var registerFormatType = richText.registerFormatType;
  var insert = richText.insert;
  var RichTextToolbarButton = blockEditor.RichTextToolbarButton;
  var createElement = element.createElement;
  var __ = i18n.__;

  var pendingInsert = null;

  var downloadSvg = createElement(
    "svg",
    {
      xmlns: "http://www.w3.org/2000/svg",
      width: 24,
      height: 24,
      viewBox: "0 0 24 24"
    },
    createElement("path", {
      fill: "#1e1e1e",
      d: "m12 16l-5-5l1.4-1.45l2.6 2.6V4h2v8.15l2.6-2.6L17 11zm-6 4q-.825 0-1.412-.587T4 18v-3h2v3h12v-3h2v3q0 .825-.587 1.413T18 20z"
    })
  );

  function DownloadButton(props) {
    var value = props.value;
    var onChange = props.onChange;

    function handleClick() {
      pendingInsert = { value: value, onChange: onChange };
      if (window.btdlDownloadPickerOpen) {
        window.btdlDownloadPickerOpen(function (shortcode) {
          if (pendingInsert) {
            var next = insert(pendingInsert.value, shortcode);
            pendingInsert.onChange(next);
            pendingInsert = null;
          }
        });
      }
    }

    return createElement(RichTextToolbarButton, {
      icon: downloadSvg,
      title: __("Insert download", "bt-downloads"),
      onClick: handleClick,
      role: "menuitem"
    });
  }

  registerFormatType("btdl/insert-download", {
    name: "btdl/insert-download",
    title: __("Download", "bt-downloads"),
    tagName: "span",
    className: null,
    edit: DownloadButton
  });
})(
  window.wp.richText,
  window.wp.blockEditor,
  window.wp.element,
  window.wp.i18n
);
