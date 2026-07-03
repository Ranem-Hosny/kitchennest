// ============================================================
//  js/favorites.js  —  Favorites (wishlist) using localStorage
// ============================================================

const FAVORITES = (() => {
  const KEY = 'kn_favorites';

  function getIds() { return new Set(lsGet(KEY, [])); }
  function saveIds(set) { lsSet(KEY, [...set]); updateFavBadge(); }

  function has(productId) { return getIds().has(productId); }

  function toggle(productId) {
    const ids = getIds();
    if (ids.has(productId)) { ids.delete(productId); }
    else { ids.add(productId); }
    saveIds(ids);
    return ids.has(productId);
  }

  function count() { return getIds().size; }

  function getProducts() {
    return [...getIds()].map(id => getProductById(id)).filter(Boolean);
  }

  // Expose `has` as a direct call (not method) for templates
  return { has, toggle, count, getIds, getProducts };
})();

function updateFavBadge() {
  const count = FAVORITES.count();
  document.querySelectorAll('.fav-badge').forEach(el => {
    el.textContent = count;
    el.style.display = count > 0 ? 'flex' : 'none';
  });
}
