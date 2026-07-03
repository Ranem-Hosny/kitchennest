// ============================================================
//  js/components.js  —  الهيدر والفوتر المشتركة
// ============================================================

function renderHeader() {
  const currentPath = window.location.pathname.split('/').pop() || 'index.html';
  return `
  <header class="header" id="mainHeader">
    <div class="header__top">
      <div class="container header__top-inner">
        <span><i class="fa-solid fa-truck-fast"></i> شحن مجاني على منتجات مختارة</span>
        <div class="header__top-links">
          <a href="about.html">من نحن</a>
          <a href="contact.html">تواصل معنا</a>
          <a href="https://wa.me/${CONFIG.whatsappNumber}" target="_blank" class="wa-link">
            <i class="fa-brands fa-whatsapp"></i>
            واتساب
          </a>
        </div>
      </div>
    </div>
    <div class="header__main">
      <div class="container header__main-inner">
        <a href="index.html" class="logo">
          <span class="logo__icon"><i class="fa-solid fa-utensils"></i></span>
          <div>
            <span class="logo__name">${CONFIG.storeName}</span>
            <span class="logo__tag">مطبخ متميز</span>
          </div>
        </a>
        <div class="header__search">
          <form onsubmit="handleSearch(event)" class="search-form">
            <input type="text" id="searchInput" placeholder="ابحث عن أواني، مقالي، أطقم مائدة…" class="search-input" autocomplete="off">
            <button type="submit" class="search-btn" aria-label="بحث">
              <i class="fa-solid fa-magnifying-glass"></i>
            </button>
          </form>
          <div id="searchDropdown" class="search-dropdown"></div>
        </div>
        <div class="header__icons">
          <a href="favorites.html" class="header-icon-btn" title="المفضلة">
            <i class="fa-regular fa-heart"></i>
            <span class="fav-badge header-badge" style="display:none">0</span>
          </a>
          <a href="cart.html" class="header-icon-btn cart-icon-wrap" title="السلة">
            <i class="fa-solid fa-cart-shopping"></i>
            <span class="cart-badge header-badge" style="display:none">0</span>
          </a>
          <a href="https://wa.me/${CONFIG.whatsappNumber}" target="_blank" class="btn btn--wa btn--sm">
            <i class="fa-brands fa-whatsapp"></i>
            اطلب عبر واتساب
          </a>
          <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="القائمة">
            <span></span><span></span><span></span>
          </button>
        </div>
      </div>
    </div>
    <nav class="header__nav" id="mainNav">
      <div class="container header__nav-inner">
        <a href="index.html" class="${currentPath === 'index.html' ? 'active' : ''}">الرئيسية</a>
        <div class="nav-dropdown">
          <a href="category.html" class="${currentPath === 'category.html' ? 'active' : ''}">
            الفئات <i class="fa-solid fa-chevron-down" style="font-size:11px"></i>
          </a>
          <div class="nav-dropdown__menu">
            ${CATEGORIES.map(c => `<a href="category.html?cat=${c.id}">${c.icon} ${c.name}</a>`).join('')}
          </div>
        </div>
        <a href="offers.html" class="${currentPath === 'offers.html' ? 'active' : ''}" style="color:#FF6B00;font-weight:600"><i class="fa-solid fa-fire"></i> العروض</a>
        <a href="about.html" class="${currentPath === 'about.html' ? 'active' : ''}">من نحن</a>
        <a href="contact.html" class="${currentPath === 'contact.html' ? 'active' : ''}">تواصل معنا</a>
      </div>
    </nav>
    <div class="mobile-menu" id="mobileMenu">
      <a href="index.html">الرئيسية</a>
      <a href="category.html">جميع الفئات</a>
      ${CATEGORIES.map(c => `<a href="category.html?cat=${c.id}" class="mobile-menu__sub">${c.icon} ${c.name}</a>`).join('')}
      <a href="offers.html" style="color:#FF6B00"><i class="fa-solid fa-fire"></i> العروض</a>
      <a href="favorites.html"><i class="fa-regular fa-heart"></i> المفضلة</a>
      <a href="cart.html"><i class="fa-solid fa-cart-shopping"></i> السلة</a>
      <a href="about.html">من نحن</a>
      <a href="contact.html">تواصل معنا</a>
      <a href="https://wa.me/${CONFIG.whatsappNumber}" target="_blank" class="wa-link"><i class="fa-brands fa-whatsapp"></i> اطلب عبر واتساب</a>
    </div>
  </header>`;
}

function renderFooter() {
  return `
  <footer class="footer">
    <div class="container footer__grid">
      <div class="footer__brand">
        <a href="index.html" class="logo logo--light">
          <span class="logo__icon"><i class="fa-solid fa-utensils"></i></span>
          <div>
            <span class="logo__name">${CONFIG.storeName}</span>
            <span class="logo__tag">مطبخ متميز</span>
          </div>
        </a>
        <p class="footer__about">${CONFIG.storeSlogan}</p>
        <div class="footer__social">
          ${CONFIG.social.instagram !== '#' ? `<a href="${CONFIG.social.instagram}" class="social-btn" target="_blank" aria-label="إنستغرام">
            <i class="fa-brands fa-instagram"></i>
          </a>` : ''}
          ${CONFIG.social.facebook !== '#' ? `<a href="${CONFIG.social.facebook}" class="social-btn" target="_blank" aria-label="فيسبوك">
            <i class="fa-brands fa-facebook-f"></i>
          </a>` : ''}
          <a href="https://wa.me/${CONFIG.whatsappNumber}" class="social-btn social-btn--wa" target="_blank" aria-label="واتساب">
            <i class="fa-brands fa-whatsapp"></i>
          </a>
        </div>
      </div>
      <div class="footer__col">
        <h4 class="footer__heading">روابط سريعة</h4>
        <ul>
          <li><a href="index.html">الرئيسية</a></li>
          <li><a href="offers.html">العروض والخصومات</a></li>
          <li><a href="favorites.html">المفضلة</a></li>
          <li><a href="cart.html">سلة التسوق</a></li>
          <li><a href="about.html">من نحن</a></li>
          <li><a href="contact.html">تواصل معنا</a></li>
        </ul>
      </div>
      <div class="footer__col">
        <h4 class="footer__heading">الفئات</h4>
        <ul>
          ${CATEGORIES.slice(0, 8).map(c => `<li><a href="category.html?cat=${c.id}">${c.name}</a></li>`).join('')}
        </ul>
      </div>
      <div class="footer__col">
        <h4 class="footer__heading">السياسات</h4>
        <ul>
          <li><a href="privacy-policy.html">سياسة الخصوصية</a></li>
          <li><a href="terms.html">الشروط والأحكام</a></li>
          <li><a href="shipping-policy.html">الشحن والإرجاع</a></li>
          <li><a href="contact.html">المساعدة والدعم</a></li>
        </ul>
        <h4 class="footer__heading" style="margin-top:1.5rem">تواصل معنا</h4>
        <ul>
          <li><a href="mailto:${CONFIG.storeEmail}">${CONFIG.storeEmail}</a></li>
          <li><a href="tel:${CONFIG.storePhone.replace(/\s/g,'')}">${CONFIG.storePhone}</a></li>
          <li>${CONFIG.storeAddress}</li>
        </ul>
      </div>
    </div>
    <div class="footer__bottom">
      <div class="container footer__bottom-inner">
        <p>© ${new Date().getFullYear()} ${CONFIG.storeName}. جميع الحقوق محفوظة.</p>
        <div class="footer__bottom-links">
          <a href="privacy-policy.html">الخصوصية</a>
          <a href="terms.html">الشروط</a>
          <a href="shipping-policy.html">الشحن</a>
        </div>
      </div>
    </div>
  </footer>`;
}

function initPage() {
  if (!document.getElementById('fa6')) {
    const fa = document.createElement('link');
    fa.id = 'fa6'; fa.rel = 'stylesheet';
    fa.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css';
    document.head.appendChild(fa);
  }
  const headerEl = document.getElementById('siteHeader');
  const footerEl = document.getElementById('siteFooter');
  if (headerEl) headerEl.innerHTML = renderHeader();
  if (footerEl) footerEl.innerHTML = renderFooter();

  updateCartBadge();
  updateFavBadge();

  window.addEventListener('scroll', () => {
    const header = document.getElementById('mainHeader');
    if (header) header.classList.toggle('header--sticky', window.scrollY > 60);
  });

  document.addEventListener('click', e => {
    const btn = e.target.closest('#mobileMenuBtn');
    const menu = document.getElementById('mobileMenu');
    if (btn && menu) {
      menu.classList.toggle('mobile-menu--open');
      btn.classList.toggle('mobile-menu-btn--open');
    } else if (menu && !e.target.closest('.mobile-menu') && !e.target.closest('#mobileMenuBtn')) {
      menu.classList.remove('mobile-menu--open');
    }
  });

  const searchInput = document.getElementById('searchInput');
  const searchDropdown = document.getElementById('searchDropdown');
  if (searchInput && searchDropdown) {
    searchInput.addEventListener('input', debounce(() => {
      const q = searchInput.value.trim();
      if (q.length < 2) { searchDropdown.classList.remove('search-dropdown--show'); return; }
      const results = searchProducts(q).slice(0, 5);
      if (results.length === 0) {
        searchDropdown.innerHTML = '<div class="search-dropdown__empty">لا توجد نتائج</div>';
      } else {
        searchDropdown.innerHTML = results.map(p => `
          <a href="product.html?id=${p.id}" class="search-dropdown__item">
            <img src="${p.image}" alt="${p.name}" onerror="this.src='https://placehold.co/50x50/f5f5f5/999?text=+'">
            <div>
              <div class="search-dropdown__name">${p.name}</div>
              <div class="search-dropdown__price">${formatPrice(p.price)}</div>
            </div>
          </a>`).join('');
      }
      searchDropdown.classList.add('search-dropdown--show');
    }, 250));
    document.addEventListener('click', e => {
      if (!e.target.closest('.header__search')) searchDropdown.classList.remove('search-dropdown--show');
    });
  }
}

function handleSearch(e) {
  e.preventDefault();
  const q = document.getElementById('searchInput')?.value.trim();
  if (q) window.location.href = `category.html?q=${encodeURIComponent(q)}`;
}

document.addEventListener('DOMContentLoaded', initPage);
