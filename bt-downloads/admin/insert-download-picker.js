/**
 * Insert download picker modal: load list, show modal, on select call callback with shortcode.
 */
(function ($) {
  "use strict";

  var modal = null;
  var listEl = null;
  var loadingEl = null;
  var searchWrap = null;
  var searchInput = null;
  var callback = null;
  var allItems = [];
  var filteredItems = [];
  var highlightedIndex = -1;
  var picker = window.btdlDownloadPicker || {};
  var restUrl = picker.restUrl || "";
  var nonce = picker.nonce || "";

  function matchesFilter(item, q) {
    if (!q) return true;
    q = q.toLowerCase().trim();
    return (
      (item.title && item.title.toLowerCase().indexOf(q) !== -1) ||
      (item.id && String(item.id).toLowerCase().indexOf(q) !== -1)
    );
  }

  function renderList(items) {
    if (!listEl) return;
    listEl.innerHTML = "";
    filteredItems = items;
    items.forEach(function (item, idx) {
      var li = document.createElement("li");
      li.setAttribute("data-index", idx);
      li.setAttribute("data-id", item.id);
      var a = document.createElement("a");
      a.href = "#";
      a.textContent = item.title + " [" + item.id + "]";
      a.addEventListener("click", function (e) {
        e.preventDefault();
        selectItem(item.id);
      });
      li.appendChild(a);
      listEl.appendChild(li);
    });
    updateHighlight();
  }

  function updateHighlight() {
    if (!listEl) return;
    var items = listEl.querySelectorAll("li");
    items.forEach(function (li, idx) {
      li.classList.toggle(
        "btdl-download-picker-highlight",
        idx === highlightedIndex
      );
    });
    var highlighted = listEl.querySelector("li.btdl-download-picker-highlight");
    if (highlighted) {
      highlighted.scrollIntoView({ block: "nearest", behavior: "smooth" });
    }
  }

  function applyFilter() {
    var q = searchInput ? searchInput.value : "";
    var filtered = allItems.filter(function (item) {
      return matchesFilter(item, q);
    });
    highlightedIndex = -1;
    renderList(filtered);
  }

  function handleKeydown(e) {
    if (!modal || modal.style.display === "none") return;
    var items = filteredItems;
    if (items.length === 0) return;

    if (e.key === "ArrowDown") {
      e.preventDefault();
      if (highlightedIndex < items.length - 1) {
        highlightedIndex++;
        updateHighlight();
      } else if (highlightedIndex === -1) {
        highlightedIndex = 0;
        updateHighlight();
      }
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      if (highlightedIndex > 0) {
        highlightedIndex--;
        updateHighlight();
      } else if (highlightedIndex === 0) {
        highlightedIndex = -1;
        updateHighlight();
      }
    } else if (
      e.key === "Enter" &&
      highlightedIndex >= 0 &&
      items[highlightedIndex]
    ) {
      e.preventDefault();
      selectItem(items[highlightedIndex].id);
    }
  }

  function openModal(onSelect) {
    callback = onSelect;
    if (!modal) {
      modal = document.getElementById("btdl-download-picker-modal");
      listEl = modal && modal.querySelector(".btdl-download-picker-list");
      loadingEl = modal && modal.querySelector(".btdl-download-picker-loading");
      searchWrap =
        modal && modal.querySelector(".btdl-download-picker-search-wrap");
      searchInput =
        modal && modal.querySelector(".btdl-download-picker-search");
    }
    if (!modal) return;

    modal.style.display = "";
    modal.setAttribute("aria-hidden", "false");
    highlightedIndex = -1;
    if (listEl) {
      listEl.style.display = "none";
      listEl.innerHTML = "";
    }
    if (searchWrap) {
      searchWrap.style.display = "none";
      if (searchInput) searchInput.value = "";
    }
    if (loadingEl) loadingEl.style.display = "";
    allItems = [];
    filteredItems = [];

    if (!restUrl) {
      if (loadingEl) loadingEl.textContent = "REST URL not configured.";
      return;
    }

    var headers = { "Content-Type": "application/json" };
    if (nonce) headers["X-WP-Nonce"] = nonce;

    fetch(restUrl, { credentials: "same-origin", headers: headers })
      .then(function (res) {
        return res.json();
      })
      .then(function (items) {
        if (loadingEl) loadingEl.style.display = "none";
        if (!listEl) return;
        allItems = items;
        if (searchWrap) searchWrap.style.display = "block";
        listEl.style.display = "block";
        renderList(items);
        if (searchInput) {
          searchInput.focus();
        }
      })
      .catch(function () {
        if (loadingEl) loadingEl.textContent = "Failed to load downloads.";
      });
  }

  function selectItem(id) {
    var shortcode = "[download " + id + "]";
    if (typeof callback === "function") callback(shortcode);
    closeModal();
  }

  function closeModal() {
    if (modal) {
      modal.style.display = "none";
      modal.setAttribute("aria-hidden", "true");
    }
    callback = null;
  }

  $(function () {
    $(document).on(
      "click",
      ".btdl-download-picker-close, .btdl-download-picker-backdrop",
      closeModal
    );
    $(document).on("keydown", function (e) {
      if (modal && modal.style.display !== "none") {
        if (e.key === "Escape") {
          closeModal();
        } else {
          handleKeydown(e);
        }
      }
    });
    $(document).on("input", ".btdl-download-picker-search", function () {
      applyFilter();
    });
  });

  window.btdlDownloadPickerOpen = openModal;
})(jQuery);
