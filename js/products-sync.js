// ============================================================
//  js/products-sync.js — Merge live dashboard data into PRODUCTS
//  Runs synchronously right after data.js so every other script
//  (home.js, category.js, product.js, search…) sees the current
//  price / stock / image the moment it starts executing, instead
//  of the frozen values baked into data.js.
// ============================================================
(function () {
  // Admin category slugs vs. the site's original category ids
  var CATEGORY_DB_TO_SITE = {
    spoons: 'spoons-forks',
    dinnersets: 'dinner-sets',
    tools: 'kitchen-tools',
  };

  try {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'php/products-api.php?limit=200&_=' + Date.now(), false); // synchronous, cache-busted
    xhr.send(null);
    if (xhr.status !== 200) return;

    var data = JSON.parse(xhr.responseText);
    if (!data.success || !Array.isArray(data.products)) return;

    data.products.forEach(function (dbProduct) {
      var category = CATEGORY_DB_TO_SITE[dbProduct.category] || dbProduct.category;
      var existing = PRODUCTS.find(function (p) { return p.id === dbProduct.id; });

      if (existing) {
        // Dashboard is the source of truth for these editable fields
        Object.assign(existing, {
          name: dbProduct.name,
          category: category,
          subcategory: dbProduct.subcategory || existing.subcategory,
          price: dbProduct.price,
          oldPrice: dbProduct.oldPrice,
          discount: dbProduct.discount,
          shortDesc: dbProduct.shortDesc || existing.shortDesc,
          description: dbProduct.description || existing.description,
          material: dbProduct.material || existing.material,
          size: dbProduct.size || existing.size,
          color: dbProduct.color || existing.color,
          pieces: dbProduct.pieces || existing.pieces,
          image: dbProduct.image || existing.image,
          images: (dbProduct.images && dbProduct.images.length) ? dbProduct.images
                  : (dbProduct.image ? [dbProduct.image] : existing.images),
          inStock: dbProduct.inStock,
          stockQty: dbProduct.stockQty,
          isNew: dbProduct.isNew,
          isBestSeller: dbProduct.isBestSeller,
          isFeatured: dbProduct.isFeatured,
          isOffer: dbProduct.isOffer,
        });
      } else {
        // Product added purely from the dashboard — no local enrichment yet
        PRODUCTS.push(Object.assign({ tags: [] }, dbProduct, { category: category }));
      }
    });
  } catch (e) {
    console.warn('[ProductsSync] Failed to sync live product data:', e);
  }
})();

// ── Merge dashboard categories into the site's CATEGORIES ────
// Dashboard is the source of truth for the category name, image and
// display order. New categories appear on the site automatically.
(function () {
  var MAP = { spoons: 'spoons-forks', dinnersets: 'dinner-sets', tools: 'kitchen-tools' };
  var FALLBACK_IMG = '/assets/eee9761d4a6ee6e40e5d99a1a609991f.jpg';
  try {
    if (typeof CATEGORIES === 'undefined') return;
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'php/categories-api.php?_=' + Date.now(), false);
    xhr.send(null);
    if (xhr.status !== 200) return;
    var data = JSON.parse(xhr.responseText);
    if (!data.success || !Array.isArray(data.categories)) return;

    var order = [];
    data.categories.forEach(function (c) {
      var id = MAP[c.slug] || c.slug;
      order.push(id);
      var existing = CATEGORIES.find(function (x) { return x.id === id; });
      if (existing) {
        existing.name = c.name || existing.name;
        if (c.image_url) existing.img = c.image_url;   // dashboard image wins
      } else {
        CATEGORIES.push({ id: id, name: c.name, img: c.image_url || FALLBACK_IMG, icon: '', color: c.color || '#FF6B00' });
      }
    });

    // Reorder to match the dashboard's sort order (categories-api is sorted by sort_order)
    CATEGORIES.sort(function (a, b) {
      var ia = order.indexOf(a.id), ib = order.indexOf(b.id);
      if (ia === -1) ia = 999; if (ib === -1) ib = 999;
      return ia - ib;
    });
  } catch (e) {
    console.warn('[CategoriesSync] Failed to sync categories:', e);
  }
})();
