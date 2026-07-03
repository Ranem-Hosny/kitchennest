// ============================================================
//  js/category.js  —  Category page logic
// ============================================================

const ITEMS_PER_PAGE = 12;
let currentPage = 1;
let filteredProducts = [];

// Read URL params
const catId = getParam('cat');
const searchQ = getParam('q');
const filterParam = getParam('filter');

// Determine category
const category = catId ? CATEGORIES.find(c => c.id === catId) : null;

// Set page header
const pageTitle = document.getElementById('catPageTitle');
const breadcrumb = document.getElementById('catBreadcrumb');
const catBanner = document.getElementById('catBanner');

if (searchQ) {
  if (pageTitle) pageTitle.textContent = `نتائج البحث: "${searchQ}"`;
  if (breadcrumb) breadcrumb.textContent = `نتائج البحث: "${searchQ}"`;
} else if (category) {
  if (pageTitle) pageTitle.textContent = category.name;
  if (breadcrumb) breadcrumb.textContent = category.name;
  // Show banner
  if (catBanner) {
    catBanner.style.display = 'block';
    document.getElementById('catBannerImg').src = category.img;
    document.getElementById('catBannerImg').alt = category.name;
    document.getElementById('catBannerTitle').innerHTML = `${category.icon} ${category.name}`;
    const count = PRODUCTS.filter(p => p.category === catId).length;
    document.getElementById('catBannerCount').textContent = `${count} منتج متاح`;
  }
} else {
  if (pageTitle) pageTitle.textContent = filterParam === 'new' ? 'وصل حديثاً' : 'جميع المنتجات';
  if (breadcrumb) breadcrumb.textContent = filterParam === 'new' ? 'وصل حديثاً' : 'جميع المنتجات';
}

// Subcategory pills
const subcatPillsEl = document.getElementById('subcatPills');
if (subcatPillsEl && catId && SUBCATEGORIES[catId]) {
  const subs    = SUBCATEGORIES[catId];        // القيم الإنجليزية للمطابقة
  const subsAr  = SUBCATEGORIES_AR?.[catId] || subs; // العرض العربي
  let activeSub = null;
  subcatPillsEl.innerHTML = `<button class="subcat-pill subcat-pill--active" data-sub="">الكل</button>` +
    subs.map((s, i) => `<button class="subcat-pill" data-sub="${s}">${subsAr[i] || s}</button>`).join('');
  subcatPillsEl.addEventListener('click', e => {
    const btn = e.target.closest('.subcat-pill');
    if (!btn) return;
    activeSub = btn.dataset.sub || null;
    subcatPillsEl.querySelectorAll('.subcat-pill').forEach(b => b.classList.toggle('subcat-pill--active', b === btn));
    renderProducts();
  });
  subcatPillsEl.getActiveSub = () => activeSub;
}

// Material filter options
function buildMaterialFilters(products) {
  const materials = [...new Set(products.map(p => p.material.split(' with ')[0].split(' with')[0].trim()))].slice(0, 8);
  const el = document.getElementById('materialFilters');
  if (!el) return;
  el.innerHTML = materials.map(m => `
    <label class="filter-option">
      <input type="checkbox" class="mat-filter" value="${m}"> ${m}
    </label>`).join('');
}

// Get base products
function getBaseProducts() {
  if (searchQ) return searchProducts(searchQ);
  if (filterParam === 'new') return getNewArrivals(50);
  if (catId) return getProductsByCategory(catId);
  return PRODUCTS;
}

// Apply all filters
function applyFilters(products) {
  let result = [...products];

  // Subcategory
  const activeSub = subcatPillsEl?.getActiveSub?.();
  if (activeSub) result = result.filter(p => p.subcategory === activeSub);

  // Price range
  const maxP = parseInt(document.getElementById('priceRange')?.value || 2000);
  const minInput = parseInt(document.getElementById('priceMin')?.value || 0);
  result = result.filter(p => p.price >= minInput && p.price <= maxP);

  // Rating
  const rating = parseFloat(document.querySelector('input[name="rating"]:checked')?.value || 0);
  if (rating > 0) result = result.filter(p => p.rating >= rating);

  // Stock
  if (document.getElementById('filterInStock')?.checked) result = result.filter(p => p.inStock);
  if (document.getElementById('filterOffers')?.checked) result = result.filter(p => p.discount > 0);
  if (document.getElementById('filterNew')?.checked) result = result.filter(p => p.isNew);

  // Material
  const selectedMats = [...document.querySelectorAll('.mat-filter:checked')].map(c => c.value);
  if (selectedMats.length) result = result.filter(p => selectedMats.some(m => p.material.includes(m)));

  // Sort
  const sort = document.getElementById('sortSelect')?.value || 'default';
  if (sort === 'price-asc') result.sort((a, b) => a.price - b.price);
  else if (sort === 'price-desc') result.sort((a, b) => b.price - a.price);
  else if (sort === 'rating') result.sort((a, b) => b.rating - a.rating);
  else if (sort === 'bestselling') result.sort((a, b) => b.reviewCount - a.reviewCount);
  else if (sort === 'newest') result.sort((a, b) => (b.isNew ? 1 : 0) - (a.isNew ? 1 : 0));

  return result;
}

// Render paginated products
function renderProducts() {
  const base = getBaseProducts();
  filteredProducts = applyFilters(base);

  const countEl = document.getElementById('productCount');
  if (countEl) countEl.textContent = `${filteredProducts.length} منتج`;

  const grid = document.getElementById('productGrid');
  const noResults = document.getElementById('noResults');

  if (filteredProducts.length === 0) {
    grid.innerHTML = '';
    noResults.style.display = 'block';
  } else {
    noResults.style.display = 'none';
    const start = (currentPage - 1) * ITEMS_PER_PAGE;
    const pageItems = filteredProducts.slice(start, start + ITEMS_PER_PAGE);
    grid.innerHTML = pageItems.map(p => buildProductCard(p)).join('');
  }

  renderPagination();
}

function renderPagination() {
  const total = Math.ceil(filteredProducts.length / ITEMS_PER_PAGE);
  const el = document.getElementById('pagination');
  if (!el) return;
  if (total <= 1) { el.innerHTML = ''; return; }

  let html = '';
  // Prev
  html += `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="goPage(${currentPage - 1})">‹</button>`;
  for (let i = 1; i <= total; i++) {
    if (i === 1 || i === total || Math.abs(i - currentPage) <= 1) {
      html += `<button class="page-btn ${i === currentPage ? 'page-btn--active' : ''}" onclick="goPage(${i})">${i}</button>`;
    } else if (Math.abs(i - currentPage) === 2) {
      html += `<button class="page-btn" disabled>…</button>`;
    }
  }
  html += `<button class="page-btn" ${currentPage === total ? 'disabled' : ''} onclick="goPage(${currentPage + 1})">›</button>`;
  el.innerHTML = html;
}

function goPage(n) {
  currentPage = n;
  renderProducts();
  window.scrollTo({ top: 200, behavior: 'smooth' });
}

// Initialize
const base = getBaseProducts();
buildMaterialFilters(base);
renderProducts();

// Event listeners
document.getElementById('priceRange')?.addEventListener('input', function () {
  document.getElementById('priceRangeVal').textContent = this.value + ' ج.م';
  document.getElementById('priceMax').value = this.value;
});
document.getElementById('priceMax')?.addEventListener('input', function () {
  document.getElementById('priceRange').value = this.value;
  document.getElementById('priceRangeVal').textContent = this.value + ' ج.م';
});
document.getElementById('applyFilters')?.addEventListener('click', () => { currentPage = 1; renderProducts(); });
document.getElementById('clearFilters')?.addEventListener('click', () => {
  document.querySelectorAll('.filter-sidebar input').forEach(i => {
    if (i.type === 'checkbox' || i.type === 'radio') i.checked = false;
    else i.value = '';
  });
  document.getElementById('priceRange').value = 2000;
  document.getElementById('priceRangeVal').textContent = '2000 ج.م';
  document.getElementById('sortSelect').value = 'default';
  currentPage = 1;
  renderProducts();
});
document.getElementById('resetResults')?.addEventListener('click', () => {
  document.getElementById('clearFilters')?.click();
});
document.getElementById('sortSelect')?.addEventListener('change', () => { currentPage = 1; renderProducts(); });

// Filter toggle (mobile)
document.getElementById('filterToggleBtn')?.addEventListener('click', () => {
  document.getElementById('filterSidebar')?.classList.toggle('filter-sidebar--open');
});

// Grid/List view toggle
document.getElementById('gridView')?.addEventListener('click', function () {
  document.getElementById('productGrid').className = 'product-grid';
  this.classList.add('view-btn--active');
  document.getElementById('listView')?.classList.remove('view-btn--active');
});
document.getElementById('listView')?.addEventListener('click', function () {
  document.getElementById('productGrid').className = 'product-grid product-grid--3';
  this.classList.add('view-btn--active');
  document.getElementById('gridView')?.classList.remove('view-btn--active');
});
