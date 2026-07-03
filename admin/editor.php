<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/admin-config.php';
require_once 'includes/auth.php';

$pageTitle    = 'محرر الموقع';
$currentPage  = 'editor';
$pageSubtitle = 'عدّل نصوص وصور الصفحة الرئيسية مباشرةً';

include 'includes/layout-start.php';
?>

<style>
  .ed-toolbar { position: sticky; top: 0; z-index: 30; display:flex; flex-wrap:wrap; align-items:center; gap:10px;
    background:#fff; border:1px solid var(--border); border-radius:14px; padding:12px 16px; margin-bottom:16px; box-shadow:var(--shadow-sm); }
  .ed-toolbar .ed-hint { color:var(--text-muted); font-size:13px; }
  .ed-spacer { flex:1; }
  .ed-badge { font-size:12px; font-weight:700; padding:3px 10px; border-radius:20px; }
  .ed-badge.on  { background:var(--success-bg); color:#15803D; }
  .ed-badge.off { background:var(--bg); color:var(--text-muted); }
  .ed-dirty { color:var(--primary); font-weight:700; font-size:13px; }
  .ed-frame-wrap { border:1px solid var(--border); border-radius:14px; overflow:hidden; background:#fff; }
  .ed-frame-wrap.mobile { max-width:420px; margin:0 auto; }
  #siteFrame { width:100%; height:78vh; border:0; display:block; }
  .ed-sections { display:none; background:#fff; border:1px solid var(--border); border-radius:14px; padding:14px 16px; margin-bottom:16px; }
  .ed-sections.show { display:block; }
  .ed-sections h4 { font-size:14px; margin-bottom:10px; }
  .ed-sec-item { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:8px 0; border-bottom:1px solid var(--border-light); }
  .ed-sec-item:last-child { border-bottom:none; }
  .ed-img-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(120px,1fr)); gap:12px; }
  .ed-img-card { border:1px solid var(--border); border-radius:10px; overflow:hidden; text-align:center; background:var(--bg); }
  .ed-img-card img { width:100%; height:80px; object-fit:cover; display:block; background:#eee; }
  .ed-img-card button { width:100%; border:0; border-top:1px solid var(--border); background:#fff; padding:7px; font-size:12px; font-weight:600; color:var(--primary); cursor:pointer; font-family:inherit; }
  .ed-img-card button:hover { background:var(--primary-bg); }
  .ed-img-card span { font-size:10px; color:var(--text-muted); display:block; padding:3px; }
  .ed-switch { position:relative; width:44px; height:24px; flex-shrink:0; }
  .ed-switch input { opacity:0; width:0; height:0; }
  .ed-slider { position:absolute; inset:0; background:#cbd5e1; border-radius:24px; cursor:pointer; transition:.2s; }
  .ed-slider:before { content:''; position:absolute; width:18px; height:18px; right:3px; top:3px; background:#fff; border-radius:50%; transition:.2s; }
  .ed-switch input:checked + .ed-slider { background:var(--success); }
  .ed-switch input:checked + .ed-slider:before { transform:translateX(-20px); }
  /* image-replace dialog */
  .ed-modal { position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:400; display:none; align-items:center; justify-content:center; padding:20px; }
  .ed-modal.show { display:flex; }
  .ed-modal__box { background:#fff; border-radius:16px; padding:24px; width:100%; max-width:420px; }
  .ed-modal__box h4 { margin-bottom:14px; }
</style>

<div class="ed-toolbar">
  <select id="edPage" class="form-control" style="width:auto;min-width:150px;font-weight:600;">
    <option value="index">🏠 الصفحة الرئيسية</option>
    <option value="about">من نحن</option>
    <option value="contact">تواصل معنا</option>
    <option value="offers">العروض</option>
    <option value="privacy-policy">سياسة الخصوصية</option>
    <option value="shipping-policy">سياسة الشحن</option>
    <option value="terms">الشروط والأحكام</option>
  </select>
  <button class="btn btn-primary btn-sm" id="edToggle"><i class="fas fa-pen"></i> تفعيل التعديل</button>
  <button class="btn btn-outline btn-sm" id="edSections"><i class="fas fa-layer-group"></i> السكاشن</button>
  <button class="btn btn-outline btn-sm" id="edImages"><i class="fas fa-images"></i> الصور</button>
  <button class="btn btn-outline btn-sm" id="edDevice" title="معاينة الموبايل"><i class="fas fa-mobile-screen"></i></button>
  <span class="ed-badge off" id="edMode">وضع المعاينة</span>
  <span class="ed-dirty" id="edDirty"></span>
  <span class="ed-spacer"></span>
  <span class="ed-hint">اضغط على أي نص لتعديله، وأي صورة لتغييرها</span>
  <button class="btn btn-primary btn-sm" id="edSave" disabled><i class="fas fa-save"></i> حفظ التغييرات</button>
</div>

<div class="ed-sections" id="edSectionsPanel">
  <h4><i class="fas fa-layer-group" style="color:var(--primary)"></i> إظهار / إخفاء أقسام الصفحة</h4>
  <div id="edSectionsList"><div class="text-muted" style="font-size:13px">فعّل التعديل أولاً لعرض الأقسام.</div></div>
</div>

<div class="ed-sections" id="edImagesPanel">
  <h4><i class="fas fa-images" style="color:var(--primary)"></i> كل صور الصفحة — اضغط "تغيير" على أي صورة</h4>
  <div class="ed-img-grid" id="edImagesList"><div class="text-muted" style="font-size:13px">فعّل التعديل أولاً لعرض الصور.</div></div>
</div>

<div class="ed-frame-wrap" id="edFrameWrap">
  <iframe id="siteFrame" src="../index.html?edit=1"></iframe>
</div>

<div class="ed-modal" id="edImgModal">
  <div class="ed-modal__box">
    <h4><i class="fas fa-image" style="color:var(--info)"></i> تغيير الصورة</h4>
    <div class="form-group">
      <label class="form-label">رفع صورة من الجهاز</label>
      <input type="file" id="edImgFile" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif">
    </div>
    <div class="form-group">
      <label class="form-label">أو رابط صورة</label>
      <input type="url" id="edImgUrl" class="form-control" placeholder="https://...">
    </div>
    <div id="edImgStatus" style="font-size:13px;margin-bottom:10px;color:var(--text-muted)"></div>
    <div style="display:flex;gap:8px;">
      <button class="btn btn-primary btn-sm" id="edImgApply" style="flex:1;justify-content:center;">تطبيق</button>
      <button class="btn btn-ghost btn-sm" id="edImgCancel">إلغاء</button>
    </div>
  </div>
</div>

<script>
(function () {
  var PAGE = 'index';
  var frame = document.getElementById('siteFrame');

  function resetEditUI(){
    editing = false; dirty = {}; refreshDirty();
    var t = document.getElementById('edToggle');
    t.innerHTML = '<i class="fas fa-pen"></i> تفعيل التعديل';
    t.classList.remove('btn-outline'); t.classList.add('btn-primary');
    var m = document.getElementById('edMode'); m.className = 'ed-badge off'; m.textContent = 'وضع المعاينة';
    document.getElementById('edSectionsPanel').classList.remove('show');
    document.getElementById('edImagesPanel').classList.remove('show');
  }

  document.getElementById('edPage').addEventListener('change', function(){
    if (Object.keys(dirty).length && !confirm('لديك تغييرات غير محفوظة — الانتقال سيتجاهلها. متابعة؟')) {
      this.value = PAGE; return;
    }
    PAGE = this.value;
    resetEditUI();
    frame.src = '../' + PAGE + '.html?edit=1';
  });
  var dirty = {};                 // key -> {key, value, type}
  var editing = false;
  var currentImg = null;
  var currentThumb = null;

  var DYNAMIC = '[id$="Grid"], .cat-slider, .cat-slider-wrap, .banners-grid, .reviews-grid, .collections-grid, .product-grid, #siteHeader, #siteFooter';
  var TEXT_TAGS = {H1:1,H2:1,H3:1,H4:1,H5:1,H6:1,P:1,SPAN:1,A:1,BUTTON:1,LI:1};
  var INLINE    = {SPAN:1,I:1,B:1,STRONG:1,EM:1,BR:1,SMALL:1,U:1};

  function doc(){ return frame.contentDocument || frame.contentWindow.document; }

  // Stable CSS-path key — must match how content-sync.js re-selects on the site
  function getPath(el){
    var parts = [];
    while (el && el.nodeType === 1 && el.tagName !== 'BODY' && el.tagName !== 'HTML') {
      var idx = 1, sib = el;
      while (sib = sib.previousElementSibling) { if (sib.tagName === el.tagName) idx++; }
      parts.unshift(el.tagName.toLowerCase() + ':nth-of-type(' + idx + ')');
      el = el.parentElement;
    }
    return 'body>' + parts.join('>');
  }

  function isEditableText(el){
    if (!TEXT_TAGS[el.tagName]) return false;
    if (el.closest(DYNAMIC)) return false;
    if (!el.textContent.trim()) return false;
    for (var i = 0; i < el.children.length; i++) { if (!INLINE[el.children[i].tagName]) return false; }
    return true;
  }

  function setDirty(key, value, type){ dirty[key] = {key:key, value:value, type:type}; refreshDirty(); }
  function refreshDirty(){
    var n = Object.keys(dirty).length;
    document.getElementById('edDirty').textContent = n ? ('● ' + n + ' تغيير غير محفوظ') : '';
    document.getElementById('edSave').disabled = n === 0;
  }

  function injectStyle(){
    var d = doc();
    if (d.getElementById('edStyle')) return;
    var s = d.createElement('style'); s.id = 'edStyle';
    s.textContent =
      '[data-ed]{outline:1px dashed rgba(255,107,0,.5);outline-offset:2px;cursor:text;transition:outline .15s;}' +
      '[data-ed]:hover{outline:2px solid #FF6B00;background:rgba(255,107,0,.05);}' +
      '[data-ed="image"]{cursor:pointer;}' +
      '[data-ed][contenteditable="true"]:focus{outline:2px solid #FF6B00;background:rgba(255,107,0,.08);}';
    d.head.appendChild(s);
  }
  function removeStyle(){ var d=doc(), s=d.getElementById('edStyle'); if(s) s.remove(); }

  function enableEdit(){
    var d = doc();
    d.querySelectorAll('h1,h2,h3,h4,h5,h6,p,span,a,button,li').forEach(function(el){
      if (!isEditableText(el)) return;
      el.setAttribute('data-ed','text');
      el.setAttribute('contenteditable','true');
      el.addEventListener('input', onText);
      el.addEventListener('keydown', function(e){ if(e.key==='Enter' && el.tagName!=='P'){ e.preventDefault(); } });
    });
    d.querySelectorAll('img').forEach(function(el){
      if (el.closest(DYNAMIC)) return;
      el.setAttribute('data-ed','image');
      el.addEventListener('click', onImg, true);
    });
    // block navigation while editing
    d.addEventListener('click', function(e){
      if (!editing) return;
      var a = e.target.closest('a,button');
      if (a && !e.target.closest('img')) { e.preventDefault(); }
    }, true);
    injectStyle();
    buildSections();
    buildImages();
    editing = true;
  }

  function disableEdit(){
    var d = doc();
    d.querySelectorAll('[data-ed]').forEach(function(el){
      el.removeAttribute('contenteditable');
      el.removeAttribute('data-ed');
    });
    removeStyle();
    editing = false;
  }

  function onText(e){ var el=e.currentTarget; setDirty(getPath(el), el.innerHTML, 'text'); }

  function onImg(e){
    if (!editing) return;
    e.preventDefault(); e.stopPropagation();
    openImgDialog(e.currentTarget, null);
  }

  function openImgDialog(img, thumbEl){
    currentImg = img; currentThumb = thumbEl || null;
    document.getElementById('edImgUrl').value = '';
    document.getElementById('edImgFile').value = '';
    document.getElementById('edImgStatus').textContent = '';
    document.getElementById('edImgModal').classList.add('show');
  }

  // ── Sections show/hide ────────────────────────────────────
  function buildSections(){
    var d = doc();
    var list = document.getElementById('edSectionsList');
    list.innerHTML = '';
    var secs = d.querySelectorAll('body > section');
    secs.forEach(function(sec, i){
      var titleEl = sec.querySelector('.section__title, .hero__title, .wa-cta__title, .offers-header__title');
      var label = titleEl ? titleEl.textContent.trim().slice(0, 40) : ('قسم ' + (i+1));
      var key = getPath(sec);
      var visible = sec.style.display !== 'none';
      var row = document.createElement('div'); row.className = 'ed-sec-item';
      row.innerHTML = '<span style="font-size:13px;font-weight:600">' + label + '</span>' +
        '<label class="ed-switch"><input type="checkbox" ' + (visible?'checked':'') + '><span class="ed-slider"></span></label>';
      row.querySelector('input').addEventListener('change', function(){
        var show = this.checked;
        sec.style.display = show ? '' : 'none';
        setDirty(key, show ? 'shown' : 'hidden', 'section');
      });
      list.appendChild(row);
    });
    if (!secs.length) list.innerHTML = '<div class="text-muted" style="font-size:13px">لا توجد أقسام.</div>';
  }

  // ── Images list (reliable way to change any image incl. hero) ─
  function buildImages(){
    var d = doc();
    var list = document.getElementById('edImagesList');
    list.innerHTML = '';
    var imgs = [].filter.call(d.querySelectorAll('img'), function(im){ return !im.closest(DYNAMIC); });
    if (!imgs.length) { list.innerHTML = '<div class="text-muted" style="font-size:13px">لا توجد صور قابلة للتعديل في هذه الصفحة.</div>'; return; }
    imgs.forEach(function(im, i){
      var card = document.createElement('div'); card.className = 'ed-img-card';
      var label = (im.getAttribute('alt') || ('صورة ' + (i+1))).slice(0, 24);
      var thumb = document.createElement('img'); thumb.src = im.src; thumb.alt = label;
      var name = document.createElement('span'); name.textContent = label;
      var btn = document.createElement('button'); btn.type = 'button'; btn.innerHTML = '<i class="fas fa-arrows-rotate"></i> تغيير';
      btn.addEventListener('click', function(){ openImgDialog(im, thumb); });
      card.appendChild(thumb); card.appendChild(name); card.appendChild(btn);
      list.appendChild(card);
    });
  }

  // ── Save ──────────────────────────────────────────────────
  function save(){
    var items = Object.keys(dirty).map(function(k){ return dirty[k]; });
    if (!items.length) return;
    var btn = document.getElementById('edSave');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جارٍ الحفظ...';
    fetch('../php/save-content.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ page: PAGE, items: items })
    }).then(function(r){ return r.json(); }).then(function(res){
      btn.innerHTML = '<i class="fas fa-save"></i> حفظ التغييرات';
      if (res.success) { dirty = {}; refreshDirty(); toast('تم حفظ التغييرات ✓ ستظهر على الموقع مباشرةً'); }
      else { toast('تعذّر الحفظ: ' + (res.message||''), true); btn.disabled = false; }
    }).catch(function(){ btn.innerHTML='<i class="fas fa-save"></i> حفظ التغييرات'; btn.disabled=false; toast('خطأ في الاتصال', true); });
  }

  function toast(msg, err){
    if (typeof showToast === 'function') { showToast(msg, err?'error':'success'); return; }
    alert(msg);
  }

  // ── Image dialog actions ──────────────────────────────────
  function applyImg(url){
    if (!currentImg) return;
    currentImg.src = url;
    if (currentThumb) currentThumb.src = url;
    setDirty(getPath(currentImg), url, 'image');
    document.getElementById('edImgModal').classList.remove('show');
  }
  document.getElementById('edImgApply').addEventListener('click', function(){
    var file = document.getElementById('edImgFile').files[0];
    var url  = document.getElementById('edImgUrl').value.trim();
    if (file) {
      var st = document.getElementById('edImgStatus'); st.textContent = 'جارٍ رفع الصورة...';
      var fd = new FormData(); fd.append('image', file);
      fetch('../php/save-content.php?action=upload', { method:'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(res){ if(res.success){ applyImg(res.url); } else { st.textContent = res.message||'فشل الرفع'; } })
        .catch(function(){ st.textContent = 'خطأ في الرفع'; });
    } else if (url) { applyImg(url); }
    else { document.getElementById('edImgStatus').textContent = 'اختر صورة أو أدخل رابطاً.'; }
  });
  document.getElementById('edImgCancel').addEventListener('click', function(){ document.getElementById('edImgModal').classList.remove('show'); });

  // ── Toolbar wiring ────────────────────────────────────────
  document.getElementById('edToggle').addEventListener('click', function(){
    if (editing) {
      disableEdit();
      this.innerHTML = '<i class="fas fa-pen"></i> تفعيل التعديل'; this.classList.remove('btn-outline'); this.classList.add('btn-primary');
      document.getElementById('edMode').className = 'ed-badge off'; document.getElementById('edMode').textContent = 'وضع المعاينة';
    } else {
      enableEdit();
      this.innerHTML = '<i class="fas fa-eye"></i> إنهاء التعديل'; this.classList.remove('btn-primary'); this.classList.add('btn-outline');
      document.getElementById('edMode').className = 'ed-badge on'; document.getElementById('edMode').textContent = 'وضع التعديل';
    }
  });
  document.getElementById('edSections').addEventListener('click', function(){
    document.getElementById('edSectionsPanel').classList.toggle('show');
  });
  document.getElementById('edImages').addEventListener('click', function(){
    var p = document.getElementById('edImagesPanel');
    p.classList.toggle('show');
    if (p.classList.contains('show') && editing) buildImages();
  });
  document.getElementById('edDevice').addEventListener('click', function(){
    document.getElementById('edFrameWrap').classList.toggle('mobile');
  });
  document.getElementById('edSave').addEventListener('click', save);

  window.addEventListener('beforeunload', function(e){
    if (Object.keys(dirty).length) { e.preventDefault(); e.returnValue = ''; }
  });
})();
</script>

<?php include 'includes/layout-end.php'; ?>
