// ============================================================
//  js/product.js  —  Product detail page logic
// ============================================================

const productId = parseInt(getParam('id'));
const product = getProductById(productId);

if (!product) {
  document.getElementById('productDetail').innerHTML = `
    <div style="text-align:center;padding:60px 20px;grid-column:1/-1">
      <div style="font-size:48px;margin-bottom:12px">😕</div>
      <h2>المنتج غير موجود</h2>
      <p style="color:var(--text-secondary);margin:8px 0 20px">هذا المنتج غير موجود أو تم إزالته.</p>
      <a href="category.html" class="btn btn--primary">تصفح المنتجات</a>
    </div>`;
} else {
  // Track recently viewed
  addToRecentlyViewed(product.id);

  // Breadcrumb
  const cat = CATEGORIES.find(c => c.id === product.category);
  document.title = `${product.name} — بيت العوضي`;
  const bCat = document.getElementById('breadCat');
  if (bCat) { bCat.textContent = cat?.name; bCat.href = `category.html?cat=${product.category}`; }
  document.getElementById('breadProduct').textContent = product.name;

  // Gallery
  const mainImg = document.getElementById('mainImg');
  const thumbsEl = document.getElementById('thumbs');
  mainImg.src = product.image;
  mainImg.alt = product.name;

  const allImgs = product.images.length ? product.images : [product.image];
  if (allImgs.length > 1) {
    thumbsEl.innerHTML = allImgs.map((src, i) => `
      <div class="product-gallery__thumb ${i === 0 ? 'product-gallery__thumb--active' : ''}"
           data-idx="${i}">
        <img src="${src}" alt="${product.name} ${i + 1}"
             onerror="this.src='https://placehold.co/80x80/f5f5f5/999?text=+'">
      </div>`).join('');
    thumbsEl.addEventListener('click', e => {
      const thumb = e.target.closest('.product-gallery__thumb');
      if (!thumb) return;
      const idx = parseInt(thumb.dataset.idx);
      mainImg.src = allImgs[idx];
      thumbsEl.querySelectorAll('.product-gallery__thumb').forEach((t, i) =>
        t.classList.toggle('product-gallery__thumb--active', i === idx));
    });
  }

  // Info
  const infoCategory = document.getElementById('infoCategory');
  infoCategory.textContent = cat?.name;
  infoCategory.href = `category.html?cat=${product.category}`;
  document.getElementById('infoTitle').textContent = product.name;
  document.getElementById('infoStars').innerHTML = renderStars(product.rating, 'md');
  document.getElementById('infoRating').textContent = product.rating.toFixed(1);
  document.getElementById('infoReviewCount').textContent = `(${product.reviewCount} تقييم)`;
  document.getElementById('infoPrice').textContent = formatPrice(product.price);

  if (product.oldPrice) {
    document.getElementById('infoOldPrice').textContent = formatPrice(product.oldPrice);
    document.getElementById('infoOldPrice').style.display = '';
    document.getElementById('infoDiscount').textContent = `-${product.discount}%`;
    document.getElementById('infoDiscount').style.display = '';
    const savings = product.oldPrice - product.price;
    document.getElementById('infoSavings').textContent = `وفّرت ${formatPrice(savings)}!`;
    document.getElementById('infoSavings').style.display = '';
  }

  document.getElementById('infoDesc').textContent = product.shortDesc;

  // Meta
  const metaEl = document.getElementById('productMeta');
  const metaItems = [
    { label: 'المادة', value: product.material },
    { label: 'المقاس', value: product.size },
    { label: 'اللون', value: product.color },
    { label: 'عدد القطع', value: product.pieces },
  ].filter(m => m.value);
  metaEl.innerHTML = metaItems.map(m => `
    <div class="product-meta__item">
      <span class="product-meta__label">${m.label}</span>
      <span class="product-meta__value">${m.value}</span>
    </div>`).join('');

  // Stock — show the actual quantity when known (dashboard products)
  const stockEl = document.getElementById('infoStock');
  const qty = (typeof product.stockQty === 'number') ? product.stockQty : null;
  if (!product.inStock || qty === 0) {
    stockEl.innerHTML = `<span class="stock-dot stock-dot--out"></span> غير متوفر حالياً`;
    stockEl.style.color = 'var(--danger)';
  } else if (qty !== null && qty > 0) {
    if (qty <= 5) {
      stockEl.innerHTML = `<span class="stock-dot stock-dot--low"></span> باقٍ ${qty} ${qty === 1 ? 'قطعة' : 'قطع'} فقط في المخزون`;
      stockEl.style.color = 'var(--warning, #B45309)';
    } else {
      stockEl.innerHTML = `<span class="stock-dot"></span> متوفر — ${qty} قطعة في المخزون`;
      stockEl.style.color = 'var(--success)';
    }
  } else {
    stockEl.innerHTML = `<span class="stock-dot"></span> متوفر — جاهز للشحن`;
    stockEl.style.color = 'var(--success)';
  }

  // Shipping fee note
  document.getElementById('shippingFeeNote').textContent = formatPrice(CONFIG.shippingFee);

  // Quantity controls
  const qtyInput = document.getElementById('qtyInput');
  document.getElementById('qtyMinus').addEventListener('click', () => {
    if (parseInt(qtyInput.value) > 1) qtyInput.value = parseInt(qtyInput.value) - 1;
  });
  document.getElementById('qtyPlus').addEventListener('click', () => {
    if (parseInt(qtyInput.value) < 99) qtyInput.value = parseInt(qtyInput.value) + 1;
  });

  // Add to cart
  document.getElementById('addToCartBtn').addEventListener('click', () => {
    CART.add(product, parseInt(qtyInput.value));
    showToast(`"${product.name}" أضيف للسلة!`);
  });

  // Add to favorites
  const favBtn = document.getElementById('addToFavBtn');
  function updateFavBtn() {
    const isFav = FAVORITES.has(product.id);
    favBtn.innerHTML = `<i class="fa-${isFav ? 'solid' : 'regular'} fa-heart"></i> ${isFav ? 'محفوظ' : 'حفظ'}`;
    favBtn.style.color = isFav ? 'var(--primary)' : '';
  }
  updateFavBtn();
  favBtn.addEventListener('click', () => {
    FAVORITES.toggle(product.id);
    updateFavBtn();
    showToast(FAVORITES.has(product.id) ? 'أضيف للمفضلة!' : 'أُزيل من المفضلة');
  });

  // WhatsApp quick order button
  const waMsg = `مرحباً ${CONFIG.storeName}! أود الاستفسار عن:\n*${product.name}*\nالسعر: ${formatPrice(product.price)}\n\nيرجى تأكيد التوفر. شكراً!`;
  document.getElementById('waOrderBtn').href = `https://wa.me/${CONFIG.whatsappNumber}?text=${encodeURIComponent(waMsg)}`;

  // Tabs
  document.getElementById('tabDesc').textContent = product.description;
  document.getElementById('featuresList').innerHTML = product.features.map(f => `<li>${f}</li>`).join('');
  document.getElementById('specsTable').innerHTML = Object.entries(product.specs).map(([k, v]) =>
    `<tr><td>${k}</td><td>${v}</td></tr>`).join('');

  // Reviews
  const totalReviews = product.reviews.length;
  const avgRating = totalReviews
    ? (product.reviews.reduce((s, r) => s + r.rating, 0) / totalReviews).toFixed(1)
    : product.rating;

  document.getElementById('reviewsSummary').innerHTML = `
    <div class="reviews-summary__score">
      <div class="reviews-summary__number">${avgRating}</div>
      <div style="margin:6px 0">${renderStars(parseFloat(avgRating), 'md')}</div>
      <div class="reviews-summary__total">${product.reviewCount} تقييم</div>
    </div>
    <div class="reviews-bars">
      ${[5,4,3,2,1].map(s => {
        const c = product.reviews.filter(r => r.rating === s).length || Math.round(product.reviewCount * [0.55,0.25,0.12,0.05,0.03][5-s]);
        const pct = Math.round((c / product.reviewCount) * 100);
        return `<div class="rating-bar">
          <span style="width:30px;text-align:right;color:var(--text-muted);font-size:12px">${s}★</span>
          <div class="rating-bar__track"><div class="rating-bar__fill" style="width:${pct}%"></div></div>
          <span style="width:36px;font-size:12px;color:var(--text-muted)">${pct}%</span>
        </div>`;
      }).join('')}
    </div>`;

  if (product.reviews.length > 0) {
    document.getElementById('productReviews').innerHTML = product.reviews.map(r => `
      <div class="review-card" style="margin-bottom:16px">
        <div class="review-card__header">
          <div class="review-card__avatar">${r.name[0]}</div>
          <div>
            <div class="review-card__name">${r.name}</div>
            <div style="display:flex;gap:3px;margin:3px 0">${renderStars(r.rating)}</div>
            <div class="review-card__date">${r.date}</div>
          </div>
        </div>
        <p class="review-card__comment">${r.comment}</p>
      </div>`).join('');
  } else {
    document.getElementById('productReviews').innerHTML = `
      <div style="text-align:center;padding:40px;color:var(--text-muted)">
        لا توجد مراجعات مكتوبة بعد. كن أول من يقيّم هذا المنتج!
      </div>`;
  }

  // Tabs click
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('tab-btn--active'));
      document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('tab-panel--active'));
      btn.classList.add('tab-btn--active');
      document.getElementById('tab-' + btn.dataset.tab)?.classList.add('tab-panel--active');
    });
  });

  // Similar products
  const similar = getSimilarProducts(product, 4);
  document.getElementById('similarGrid').innerHTML = similar.map(p => buildProductCard(p)).join('');

  // Recently viewed
  const recent = getRecentlyViewed().filter(p => p.id !== product.id).slice(0, 4);
  if (recent.length > 0) {
    document.getElementById('recentSection').style.display = '';
    document.getElementById('recentGrid').innerHTML = recent.map(p => buildProductCard(p)).join('');
  }
}
