// ============================================================
//  js/config.js  —  CHANGE THESE VALUES BEFORE GOING LIVE
// ============================================================

const CONFIG = {
  /* ── WhatsApp number that receives all orders ─────────────
     Format: country-code + number, NO + sign, NO spaces
     Egypt example: 201001234567  (= +20 100 123 4567)      */
  whatsappNumber: '201551677016',          // ✅ تم التعيين

  /* ── InstaPay phone number (for shipping fee payment) ─────
     Customers send the exact shipping fee to this number    */
  instaPayNumber: '01551677016',           // ✅ تم التعيين

  /* ── Shipping fee in EGP ──────────────────────────────────
     Set to 0 for free shipping on all orders               */
  shippingFee: 50,                         // ← CHANGE THIS

  /* ── Offers countdown duration (hours) ───────────────────── */
  offerHours: 24,

  /* ── Currency label ──────────────────────────────────────── */
  currency: 'EGP',

  /* ── Store details ────────────────────────────────────────── */
  storeName: 'بيت العوضي',
  storeSlogan: 'بيتك. مطبخك. أسلوبك.',
  storeEmail: 'info@beitAlawady.com',
  storePhone: '+20 155 167 7016',
  storeWhatsApp: '+20 155 167 7016',
  storeAddress: 'القاهرة، مصر',

  /* ── Social media links (set to '#' to hide) ─────────────── */
  social: {
    instagram: 'https://www.instagram.com/samy__el_awady?igsh=MThyOGl1NnZpZDRxMQ==',
    facebook: 'https://www.facebook.com/groups/532272892339831?locale=ar_AR',
    tiktok: '#',
    twitter: '#',
  },

  /* ── Free shipping threshold (0 = always paid) ───────────── */
  freeShippingAbove: 0,
};

// ── Sync live store settings from the dashboard (DB) ─────────
// Overrides the defaults above with whatever the owner set in the
// admin panel (shipping fee, WhatsApp, store name, socials…).
(function () {
  try {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'php/settings-api.php?_=' + Date.now(), false); // sync, before other scripts use CONFIG
    xhr.send(null);
    if (xhr.status !== 200) return;
    var data = JSON.parse(xhr.responseText);
    if (!data.success || !data.settings) return;
    var s = data.settings;
    if (s.whatsapp_number)  CONFIG.whatsappNumber = s.whatsapp_number;
    if (s.instapay_number)  CONFIG.instaPayNumber = s.instapay_number;
    if (s.shipping_fee !== undefined && s.shipping_fee !== '') CONFIG.shippingFee = parseFloat(s.shipping_fee) || 0;
    if (s.offer_hours && parseInt(s.offer_hours) > 0) CONFIG.offerHours = parseInt(s.offer_hours);
    if (s.store_name)     CONFIG.storeName = s.store_name;
    if (s.store_email)    CONFIG.storeEmail = s.store_email;
    if (s.store_phone)  { CONFIG.storePhone = s.store_phone; CONFIG.storeWhatsApp = s.store_phone; }
    if (s.store_address)  CONFIG.storeAddress = s.store_address;
    if (s.social_instagram) CONFIG.social.instagram = s.social_instagram;
    if (s.social_facebook)  CONFIG.social.facebook = s.social_facebook;
    if (s.social_tiktok)    CONFIG.social.tiktok = s.social_tiktok;
  } catch (e) { console.warn('[SettingsSync] failed:', e); }
})();
