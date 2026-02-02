/**
 * Block: Download. Renders [download ID]. Editor shows a dropdown to pick a download.
 */
(function (blocks, element, blockEditor, components, i18n) {
  "use strict";

  var createElement = element.createElement;
  var useBlockProps = blockEditor.useBlockProps;
  var InspectorControls = blockEditor.InspectorControls;
  var PanelBody = components.PanelBody;
  var SelectControl = components.SelectControl;
  var Placeholder = components.Placeholder;
  var __ = i18n.__;

  var picker = window.btdlDownloadPicker || {};
  var list = picker.list || [];

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

  function getOptions() {
    var opts = [{ value: "", label: __("Select a download…", "bt-downloads") }];
    list.forEach(function (item) {
      opts.push({
        value: String(item.id),
        label: item.title + " [" + item.id + "]"
      });
    });
    return opts;
  }

  blocks.registerBlockType("btdl/download", {
    apiVersion: 2,
    title: __("Download", "bt-downloads"),
    description: __(
      "Insert a download card by selecting from your downloads.",
      "bt-downloads"
    ),
    category: "embed",
    icon: { src: downloadSvg },
    attributes: {
      downloadId: { type: "string", default: "" }
    },
    edit: function (props) {
      var blockProps = useBlockProps();
      var downloadId = props.attributes.downloadId;
      var setAttributes = props.setAttributes;
      var options = getOptions();
      var selectControl = createElement(SelectControl, {
        label: __("Download", "bt-downloads"),
        value: downloadId,
        options: options,
        onChange: function (value) {
          setAttributes({ downloadId: value || "" });
        }
      });
      var sidebar = createElement(
        InspectorControls,
        { key: "inspector" },
        createElement(
          PanelBody,
          { title: __("Download", "bt-downloads"), initialOpen: true },
          selectControl
        )
      );
      if (!downloadId) {
        return createElement(
          "div",
          blockProps,
          sidebar,
          createElement(
            Placeholder,
            { icon: downloadSvg, label: __("Download", "bt-downloads") },
            createElement(
              "p",
              null,
              __(
                "Select a download from the block settings in the sidebar.",
                "bt-downloads"
              )
            ),
            createElement(
              "div",
              { style: { marginTop: "12px", maxWidth: "280px" } },
              selectControl
            )
          )
        );
      }
      return createElement(
        "div",
        blockProps,
        sidebar,
        createElement(
          Placeholder,
          { icon: downloadSvg, label: __("Download", "bt-downloads") },
          "[download " + downloadId + "]"
        )
      );
    },
    save: function () {
      return null;
    }
  });
})(
  window.wp.blocks,
  window.wp.element,
  window.wp.blockEditor,
  window.wp.components,
  window.wp.i18n
);
