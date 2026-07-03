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
