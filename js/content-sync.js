// ============================================================
//  js/content-sync.js — Applies visual-editor overrides
//  Runs on the storefront: fetches per-element overrides for the
//  current page and applies text / image / section-visibility.
//  Keys are CSS selector paths produced by the editor (admin/editor.php).
// ============================================================
(function () {
  function apply(items) {
    items.forEach(function (it) {
      try {
        var el = document.querySelector(it.content_key);
        if (!el) return;
        if (it.type === 'image') {
          if (el.tagName === 'IMG') el.src = it.value;
          else el.style.backgroundImage = "url('" + it.value + "')";
        } else if (it.type === 'section') {
          el.style.display = (it.value === 'hidden') ? 'none' : '';
        } else {
          el.innerHTML = it.value;
        }
      } catch (e) { /* invalid selector — skip */ }
    });
  }
  try {
    var page = (document.body && document.body.getAttribute('data-page')) || 'index';
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'php/content-api.php?page=' + encodeURIComponent(page) + '&_=' + Date.now(), false);
    xhr.send(null);
    if (xhr.status !== 200) return;
    var d = JSON.parse(xhr.responseText);
    if (d.success && Array.isArray(d.content)) apply(d.content);
  } catch (e) {
    if (window.console) console.warn('[ContentSync] failed:', e);
  }
})();
