// ============================================================
//  js/home.js  —  الصفحة الرئيسية: الهيرو، البانرات، الفئات، المنتجات
// ============================================================

// ── Hero Carousel ──────────────────────────────────────────
(function () {
  const track = document.getElementById('heroTrack');
  const dotsEl = document.getElementById('heroDots');
  if (!track) return;
  const slides = track.querySelectorAll('.hero__slide');
  let current = 0;
  let autoPlay;

  slides.forEach((_, i) => {
    const d = document.createElement('button');
    d.className = 'hero__dot' + (i === 0 ? ' hero__dot--active' : '');
    d.setAttribute('aria-label', `شريحة ${i + 1}`);
    d.addEventListener('click', () => goTo(i));
    dotsEl.appendChild(d);
  });

  function goTo(n) {
    current = (n + slides.length) % slides.length;
    track.style.transform = `translateX(${current * 100}%)`;
    dotsEl.querySelectorAll('.hero__dot').forEach((d, i) =>
      d.classList.toggle('hero__dot--active', i === current));
    resetAuto();
  }

  const EDIT_MODE = location.search.indexOf('edit=1') > -1; // visual editor — freeze carousels
  function resetAuto() {
    clearInterval(autoPlay);
    if (EDIT_MODE) return;
    autoPlay = setInterval(() => goTo(current + 1), 5500);
  }

  document.getElementById('heroPrev')?.addEventListener('click', () => goTo(current - 1));
  document.getElementById('heroNext')?.addEventListener('click', () => goTo(current + 1));

  let startX = 0;
  track.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
  track.addEventListener('touchend', e => {
    const diff = startX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 50) goTo(current + (diff > 0 ? 1 : -1));
  });

  resetAuto();
})();


// ── سلايدر الفئات ─────────────────────────────────────────
const catSlider = document.getElementById('catSlider');
if (catSlider) {
  catSlider.innerHTML = CATEGORIES.map(c => `
    <a href="category.html?cat=${c.id}" class="cat-card">
      <span class="cat-card__icon">${c.img
        ? `<img src="${c.img}" alt="${c.name}" class="cat-card__img" loading="lazy">`
        : (c.icon || '<i class="fa-solid fa-tag"></i>')}</span>
      <span class="cat-card__name">${c.name}</span>
    </a>`).join('');

  document.getElementById('catLeft')?.addEventListener('click', () => {
    catSlider.scrollBy({ left: 240, behavior: 'smooth' });
    resetCatAuto();
  });
  document.getElementById('catRight')?.addEventListener('click', () => {
    catSlider.scrollBy({ left: -240, behavior: 'smooth' });
    resetCatAuto();
  });

  // ── تحريك تلقائي ──
  let catAutoTimer;
  function catAutoStep() {
    const before = catSlider.scrollLeft;
    catSlider.scrollBy({ left: -240, behavior: 'smooth' });
    setTimeout(() => {
      if (Math.abs(catSlider.scrollLeft - before) < 5) {
        catSlider.scrollTo({ left: 0, behavior: 'smooth' });
      }
    }, 500);
  }
  function startCatAuto() {
    clearInterval(catAutoTimer);
    if (location.search.indexOf('edit=1') > -1) return; // frozen in visual editor
    catAutoTimer = setInterval(catAutoStep, 1800);
  }
  function stopCatAuto() { clearInterval(catAutoTimer); }
  function resetCatAuto() { stopCatAuto(); startCatAuto(); }

  catSlider.addEventListener('mouseenter', stopCatAuto);
  catSlider.addEventListener('mouseleave', startCatAuto);
  catSlider.addEventListener('touchstart', stopCatAuto, { passive: true });
  catSlider.addEventListener('touchend', startCatAuto);

  startCatAuto();
}

// ── الأكثر مبيعاً ─────────────────────────────────────────
const bestSellersGrid = document.getElementById('bestSellersGrid');
if (bestSellersGrid) {
  bestSellersGrid.innerHTML = getBestSellers(8).map(p => buildProductCard(p)).join('');
}

// ── وصل حديثاً ────────────────────────────────────────────
const newArrivalsGrid = document.getElementById('newArrivalsGrid');
if (newArrivalsGrid) {
  newArrivalsGrid.innerHTML = getNewArrivals(5).map(p => buildProductCard(p)).join('');
}

// ── مجموعات مميزة ─────────────────────────────────────────
// تُدار من لوحة التحكم (admin/collections.php → php/collections-api.php).
// المصفوفة أدناه احتياطية فقط في حال تعذّر الوصول للـ API.
const COLLECTIONS_FALLBACK = [
  { title: 'أساسيات مطبخ رمضان', img: 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=300&fit=crop&q=80', link: 'category.html?cat=pots', count: 24 },
  { title: 'أطقم طهي عائلية', img: 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&h=300&fit=crop&q=80', link: 'category.html?cat=cookware', count: 18 },
  { title: 'أدوات خبز احترافية', img: 'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=400&h=300&fit=crop&q=80', link: 'category.html?cat=baking', count: 15 },
  { title: 'أدوات مطبخ يومية', img: 'https://images.unsplash.com/photo-1614588168022-d23f1f0f5b01?w=400&h=300&fit=crop&q=80', link: 'category.html?cat=kitchen-tools', count: 32 },
];

function loadCollections() {
  try {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'php/collections-api.php?_=' + Date.now(), false); // synchronous, cache-busted
    xhr.send(null);
    if (xhr.status === 200) {
      const data = JSON.parse(xhr.responseText);
      if (data.success && Array.isArray(data.collections) && data.collections.length) {
        return data.collections;
      }
    }
  } catch (e) {
    console.warn('[Collections] Failed to load from API, using fallback:', e);
  }
  return COLLECTIONS_FALLBACK;
}

// عدد منتجات الفئة يُحسب تلقائياً من المنتجات الفعلية (ثابتة + المضافة من اللوحة)
function collectionCount(c) {
  if (c.cat && typeof PRODUCTS !== 'undefined') {
    return PRODUCTS.filter(p => p.category === c.cat).length;
  }
  return c.count || 0;
}

const collectionsGrid = document.getElementById('collectionsGrid');
if (collectionsGrid) {
  collectionsGrid.innerHTML = loadCollections().map(c => `
    <a href="${c.link || '#'}" class="collection-card">
      <img src="${c.img}" alt="${c.title}" class="collection-card__img" loading="lazy"
           onerror="this.src='https://placehold.co/400x300/333/fff?text=${encodeURIComponent(c.title)}'">
      <div class="collection-card__overlay">
        <div class="collection-card__title">${c.title}</div>
        <div class="collection-card__count">${collectionCount(c)} منتج</div>
        <div class="collection-card__arrow">←</div>
      </div>
    </a>`).join('');
}

// رابط واتساب CTA
const waCtaBtn = document.getElementById('waCtaBtn');
if (waCtaBtn) {
  waCtaBtn.href = `https://wa.me/${CONFIG.whatsappNumber}?text=${encodeURIComponent('مرحباً ' + CONFIG.storeName + '! أود الاستفسار عن منتجاتكم.')}`;
}
