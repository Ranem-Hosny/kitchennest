// ============================================================
//  js/admin-bridge.js  —  Admin data bridge (runs after data.js / config.js)
//  Patches global variables from admin localStorage overrides
// ============================================================
(function () {
  try {
    var p = localStorage.getItem('kn_admin_products');
    if (p) { try { window.PRODUCTS = JSON.parse(p); } catch(e){} }

    var c = localStorage.getItem('kn_admin_categories');
    if (c) { try { window.CATEGORIES = JSON.parse(c); } catch(e){} }

    var cfg = localStorage.getItem('kn_admin_config');
    if (cfg) { try { Object.assign(window.CONFIG, JSON.parse(cfg)); } catch(e){} }

    var hb = localStorage.getItem('kn_admin_hero_banners');
    if (hb) { try { window.ADMIN_HERO_BANNERS = JSON.parse(hb); } catch(e){} }

    var ob = localStorage.getItem('kn_admin_offer_banners');
    if (ob) { try { window.ADMIN_OFFER_BANNERS = JSON.parse(ob); } catch(e){} }
  } catch (e) {
    console.warn('[AdminBridge] Error:', e);
  }
})();
