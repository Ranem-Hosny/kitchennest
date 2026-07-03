// ============================================================
//  js/utils.js  —  دوال مساعدة مشتركة
// ============================================================

function formatPrice(amount) {
  return `${amount.toLocaleString('ar-EG')} ${CONFIG.currency}`;
}

function renderStars(rating, size = 'sm') {
  const full = Math.floor(rating);
  const half = rating % 1 >= 0.5;
  const empty = 5 - full - (half ? 1 : 0);
  const px = size === 'sm' ? 14 : 18;
  let stars = '';
  for (let i = 0; i < full; i++) stars += `<i class="fa-solid fa-star" style="color:#FF6B00;font-size:${px}px"></i>`;
  if (half) stars += `<i class="fa-solid fa-star-half-stroke" style="color:#FF6B00;font-size:${px}px"></i>`;
  for (let i = 0; i < empty; i++) stars += `<i class="fa-regular fa-star" style="color:#E0E0E0;font-size:${px}px"></i>`;
  return stars;
}

function generateOrderNumber() {
  return 'BA' + Date.now().toString().slice(-6) + Math.floor(Math.random() * 100);
}

function lsGet(key, fallback = null) {
  try { return JSON.parse(localStorage.getItem(key)) ?? fallback; } catch { return fallback; }
}
function lsSet(key, value) {
  try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) { console.warn('localStorage error', e); }
}

let toastTimer;
function showToast(message, type = 'success') {
  let toast = document.getElementById('toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toast';
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.className = `toast toast--${type} toast--show`;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove('toast--show'), 3000);
}

function getParam(name) {
  return new URLSearchParams(window.location.search).get(name);
}

function truncate(str, n) {
  return str.length > n ? str.slice(0, n) + '…' : str;
}

function debounce(fn, ms) {
  let t;
  return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
}

function scrollTo(selector) {
  const el = document.querySelector(selector);
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function addToRecentlyViewed(productId) {
  let viewed = lsGet('kn_recent', []);
  viewed = [productId, ...viewed.filter(id => id !== productId)].slice(0, 8);
  lsSet('kn_recent', viewed);
}

function getRecentlyViewed() {
  const ids = lsGet('kn_recent', []);
  return ids.map(id => getProductById(id)).filter(Boolean);
}

function buildProductCard(product, extraClass = '') {
  const isFav = FAVORITES.has(product.id);
  const hasDiscount = product.discount > 0;
  return `
  <div class="product-card ${extraClass}" data-id="${product.id}">
    <div class="product-card__img-wrap">
      <a href="product.html?id=${product.id}">
        <img src="${product.image}" alt="${product.name}" class="product-card__img" loading="lazy"
             onerror="this.src='https://placehold.co/400x300/f5f5f5/999?text=منتج'">
      </a>
      ${hasDiscount ? `<span class="badge badge--discount">-${product.discount}%</span>` : ''}
      ${product.isNew ? `<span class="badge badge--new">جديد</span>` : ''}
      ${product.isBestSeller ? `<span class="badge badge--bestseller">الأكثر مبيعاً</span>` : ''}
      <div class="product-card__actions">
        <button class="btn-icon fav-btn ${isFav ? 'fav-btn--active' : ''}"
                onclick="toggleFavoriteCard(event, ${product.id})"
                title="${isFav ? 'إزالة من المفضلة' : 'إضافة للمفضلة'}">
          <i class="fa-${isFav ? 'solid' : 'regular'} fa-heart"></i>
        </button>
        <button class="btn-icon quick-view-btn" onclick="openQuickView(${product.id})" title="عرض سريع">
          <i class="fa-solid fa-eye"></i>
        </button>
      </div>
    </div>
    <div class="product-card__body">
      <a href="category.html?cat=${product.category}" class="product-card__cat">${CATEGORIES.find(c=>c.id===product.category)?.name || ''}</a>
      <a href="product.html?id=${product.id}" class="product-card__name">${product.name}</a>
      <p class="product-card__desc">${truncate(product.shortDesc, 70)}</p>
      <div class="product-card__rating">
        ${renderStars(product.rating)}
        <span class="product-card__review-count">(${product.reviewCount})</span>
      </div>
      <div class="product-card__price-row">
        <span class="product-card__price">${formatPrice(product.price)}</span>
        ${product.oldPrice ? `<span class="product-card__old-price">${formatPrice(product.oldPrice)}</span>` : ''}
      </div>
      <button class="btn btn--primary btn--full btn--sm add-to-cart-btn" onclick="addToCartCard(event, ${product.id})">
        <i class="fa-solid fa-cart-shopping"></i>
        أضف للسلة
      </button>
    </div>
  </div>`;
}

function addToCartCard(e, productId) {
  e.preventDefault();
  e.stopPropagation();
  const product = getProductById(productId);
  if (!product) return;
  CART.add(product, 1);
  showToast(`تمت إضافة "${product.name}" إلى السلة!`);
}

function toggleFavoriteCard(e, productId) {
  e.preventDefault();
  e.stopPropagation();
  FAVORITES.toggle(productId);
  const btn = e.currentTarget;
  const isFav = FAVORITES.has(productId);
  btn.classList.toggle('fav-btn--active', isFav);
  const icon = btn.querySelector('i');
  if (icon) icon.className = isFav ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
  showToast(isFav ? 'تمت الإضافة إلى المفضلة!' : 'تمت الإزالة من المفضلة', isFav ? 'success' : 'info');
}

function openQuickView(productId) {
  const product = getProductById(productId);
  if (!product) return;
  let modal = document.getElementById('quickViewModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'quickViewModal';
    modal.className = 'modal-overlay';
    modal.innerHTML = `<div class="modal-box quick-view-box"><button class="modal-close" onclick="closeQuickView()">✕</button><div id="quickViewContent"></div></div>`;
    modal.addEventListener('click', e => { if (e.target === modal) closeQuickView(); });
    document.body.appendChild(modal);
  }
  document.getElementById('quickViewContent').innerHTML = `
    <div class="qv-grid">
      <div class="qv-img-wrap">
        <img src="${product.image}" alt="${product.name}" class="qv-img" onerror="this.src='https://placehold.co/400x300/f5f5f5/999?text=منتج'">
      </div>
      <div class="qv-info">
        <a href="category.html?cat=${product.category}" class="product-card__cat">${CATEGORIES.find(c=>c.id===product.category)?.name || ''}</a>
        <h2 class="qv-name">${product.name}</h2>
        <div class="product-card__rating">${renderStars(product.rating, 'md')}<span class="product-card__review-count">(${product.reviewCount} تقييم)</span></div>
        <div class="qv-price-row">
          <span class="qv-price">${formatPrice(product.price)}</span>
          ${product.oldPrice ? `<span class="product-card__old-price">${formatPrice(product.oldPrice)}</span>` : ''}
          ${product.discount ? `<span class="badge badge--discount">-${product.discount}%</span>` : ''}
        </div>
        <p class="qv-desc">${product.shortDesc}</p>
        <div class="qv-meta">
          <span><strong>الخامة:</strong> ${product.material}</span>
          <span><strong>الحجم:</strong> ${product.size}</span>
          ${product.color ? `<span><strong>اللون:</strong> ${product.color}</span>` : ''}
          <span><strong>التوفر:</strong> ${product.inStock ? '✓ متاح' : '✗ نفد المخزون'}</span>
        </div>
        <div class="qv-btns">
          <button class="btn btn--primary" onclick="addToCartCard(event, ${product.id})">
            <i class="fa-solid fa-cart-shopping"></i>
            أضف للسلة
          </button>
          <a href="product.html?id=${product.id}" class="btn btn--outline">عرض التفاصيل</a>
        </div>
      </div>
    </div>`;
  modal.classList.add('modal-overlay--show');
  document.body.style.overflow = 'hidden';
}

function closeQuickView() {
  const modal = document.getElementById('quickViewModal');
  if (modal) { modal.classList.remove('modal-overlay--show'); document.body.style.overflow = ''; }
}
