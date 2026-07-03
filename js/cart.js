// ============================================================
//  js/cart.js  —  Cart logic using localStorage
// ============================================================

const CART = (() => {
  const KEY = 'kn_cart';

  function getItems() { return lsGet(KEY, []); }
  function saveItems(items) { lsSet(KEY, items); updateCartBadge(); }

  function add(product, qty = 1) {
    const items = getItems();
    const existing = items.find(i => i.id === product.id);
    if (existing) {
      existing.qty = Math.min(existing.qty + qty, 99);
    } else {
      items.push({
        id: product.id,
        name: product.name,
        price: product.price,
        image: product.image,
        category: product.category,
        qty,
      });
    }
    saveItems(items);
    animateCartIcon();
  }

  function remove(productId) {
    saveItems(getItems().filter(i => i.id !== productId));
  }

  function updateQty(productId, qty) {
    const items = getItems();
    const item = items.find(i => i.id === productId);
    if (item) { item.qty = Math.max(1, Math.min(qty, 99)); saveItems(items); }
  }

  function clear() { saveItems([]); }

  function count() { return getItems().reduce((s, i) => s + i.qty, 0); }

  function subtotal() { return getItems().reduce((s, i) => s + i.price * i.qty, 0); }

  function total() {
    const sub = subtotal();
    const shipping = (CONFIG.freeShippingAbove > 0 && sub >= CONFIG.freeShippingAbove) ? 0 : CONFIG.shippingFee;
    return sub + shipping;
  }

  return { getItems, add, remove, updateQty, clear, count, subtotal, total };
})();

function updateCartBadge() {
  const count = CART.count();
  document.querySelectorAll('.cart-badge').forEach(el => {
    el.textContent = count;
    el.style.display = count > 0 ? 'flex' : 'none';
  });
}

function animateCartIcon() {
  const icons = document.querySelectorAll('.cart-icon-wrap');
  icons.forEach(ic => {
    ic.classList.add('cart-icon-wrap--bounce');
    setTimeout(() => ic.classList.remove('cart-icon-wrap--bounce'), 600);
  });
}
