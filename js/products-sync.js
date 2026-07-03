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
          images: dbProduct.image ? [dbProduct.image] : existing.images,
          inStock: dbProduct.inStock,
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
