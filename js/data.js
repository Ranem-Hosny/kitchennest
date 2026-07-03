// ============================================================
//  js/data.js  —  All product & category data
// ============================================================

const CATEGORIES = [
  { id: 'cookware',      name: 'أواني الطهي',          icon: '<i class="fa-solid fa-utensils"></i>', color: '#FF6B00', img: '/assets/eee9761d4a6ee6e40e5d99a1a609991f.jpg' },
  { id: 'pans',         name: 'مقالي وصواني',          icon: '<i class="fa-solid fa-fire"></i>', color: '#E55A00', img: '/assets/pan.jpg' },
  { id: 'pots',         name: 'قدور وأوعية',           icon: '<i class="fa-solid fa-mug-hot"></i>', color: '#C0392B', img: '/assets/118c63e8d5e6ecfed5deb6abe17d18a8.jpg' },
  { id: 'cutlery',      name: 'سكاكين وأدوات تقطيع',  icon: '<i class="fa-solid fa-scissors"></i>', color: '#2C3E50', img: '/assets/23817701f93272599b47a8f6b169392c.jpg' },
  { id: 'spoons-forks', name: 'ملاعق وشوك',           icon: '<i class="fa-solid fa-spoon"></i>', color: '#8E44AD', img: '/assets/43ba30bd50f05189d0b4702a6038bee8.jpg' },
  { id: 'dinner-sets',  name: 'أطقم المائدة',          icon: '<i class="fa-solid fa-bowl-food"></i>', color: '#16A085', img: '/assets/115eb3f52fa60cb76cd3660f212984f2.jpg' },
  { id: 'kitchen-tools',name: 'أدوات المطبخ',          icon: '<i class="fa-solid fa-screwdriver-wrench"></i>', color: '#D35400', img: '/assets/68984d8436b10f41218978d31c677b3f.jpg' },
  { id: 'storage',      name: 'تخزين وتنظيم',         icon: '<i class="fa-solid fa-box"></i>', color: '#27AE60', img: '/assets/bfb9f54abed54dcd365952045d0236aa.jpg' },
  { id: 'baking',       name: 'أدوات الخبز',           icon: '<i class="fa-solid fa-cookie-bite"></i>', color: '#E91E63', img: '/assets/da7377d2b8808d7f2b2d23c88780fce0.jpg' },
  { id: 'serving',      name: 'أدوات التقديم',         icon: '<i class="fa-solid fa-jar"></i>', color: '#1565C0', img: '/assets/995e332af4d7a8f3cbd05c27e31da1e8.jpg' },
  { id: 'glassware',    name: 'أواني زجاجية',          icon: '<i class="fa-solid fa-wine-glass"></i>', color: '#00897B', img: '/assets/f2d5da39892babad1d2902a95dbe1b62.jpg' },
  { id: 'accessories',  name: 'إكسسوارات المطبخ',     icon: '<i class="fa-solid fa-gear"></i>', color: '#546E7A', img: '/assets/161e283154b8c46383b0ccf132252f57.jpg' },
];

// القيم الإنجليزية لازم تتطابق مع subcategory في بيانات المنتجات
const SUBCATEGORIES = {
  cookware:       ['Stainless Steel', 'Non-Stick', 'Granite', 'Pressure Cookers', 'Cooking Sets', 'Cast Iron'],
  pans:           ['Non-Stick Pans', 'Cast Iron Pans', 'Stainless Steel Pans', 'Grill Pans', 'Crepe Pans'],
  pots:           ['Stock Pots', 'Sauce Pots', 'Dutch Ovens', 'Casseroles', 'Soup Pots'],
  cutlery:        ['Chef Knives', 'Knife Sets', 'Bread Knives', 'Steak Knives', 'Knife Blocks'],
  'spoons-forks': ['Serving Spoons', 'Dessert Forks', 'Soup Spoons', 'Teaspoons', 'Cutlery Sets'],
  'dinner-sets':  ['Porcelain Sets', 'Stoneware Sets', 'Melamine Sets', 'Bone China', 'Casual Dining'],
  'kitchen-tools':['Spatulas', 'Ladles', 'Whisks', 'Tongs', 'Peelers', 'Graters'],
  storage:        ['Food Containers', 'Glass Jars', 'Organizers', 'Canisters', 'Lunch Boxes'],
  baking:         ['Baking Trays', 'Cake Pans', 'Muffin Tins', 'Rolling Pins', 'Mixing Bowls'],
  serving:        ['Serving Platters', 'Salad Bowls', 'Serving Spoons', 'Dip Sets', 'Trays'],
  glassware:      ['Drinking Glasses', 'Wine Glasses', 'Mugs', 'Carafes', 'Shot Glasses'],
  accessories:    ['Can Openers', 'Timers', 'Measuring Tools', 'Scales', 'Thermometers'],
};

// ترجمة الفئات الفرعية للعرض في الواجهة
const SUBCATEGORIES_AR = {
  cookware:       ['ستانلس ستيل', 'غير لاصق', 'جرانيت', 'قدور ضغط', 'أطقم طهي', 'حديد زهر'],
  pans:           ['مقالي غير لاصقة', 'مقالي حديد زهر', 'مقالي ستانلس ستيل', 'مقالي غريل', 'مقالي كريب'],
  pots:           ['قدور كبيرة', 'قدور صوص', 'أوعية هولندية', 'أوعية كاسرول', 'قدور حساء'],
  cutlery:        ['سكاكين طاهٍ', 'أطقم سكاكين', 'سكاكين خبز', 'سكاكين ستيك', 'حوامل سكاكين'],
  'spoons-forks': ['ملاعق تقديم', 'شوك حلويات', 'ملاعق حساء', 'ملاعق شاي', 'أطقم أدوات مائدة'],
  'dinner-sets':  ['أطقم بورسلين', 'أطقم ستون وير', 'أطقم ميلامين', 'بون تشاينا', 'أطقم يومية'],
  'kitchen-tools':['مقاشط وملاعق', 'مغارف', 'خفاقات', 'ملقاط', 'مقشرات', 'مبشرات'],
  storage:        ['حافظات طعام', 'برطمانات زجاجية', 'منظمات', 'علب تخزين', 'علب غداء'],
  baking:         ['صواني خبز', 'قوالب كيك', 'قوالب مافن', 'أعواد عجن', 'أوعية خلط'],
  serving:        ['أطباق تقديم', 'أوعية سلطة', 'ملاعق تقديم', 'أطقم صوص', 'صواني تقديم'],
  glassware:      ['كؤوس مياه', 'كؤوس عصير', 'أكواب', 'أباريق', 'كؤوس صغيرة'],
  accessories:    ['فتاحات علب', 'مؤقتات', 'أدوات قياس', 'موازين', 'ميزان حرارة'],
};

// Helper for Unsplash images
const img = (id, w = 600, h = 400) =>
  `https://images.unsplash.com/photo-${id}?w=${w}&h=${h}&fit=crop&q=80`;

const PRODUCTS = []; // العينات التجريبية اتشالت — المنتجات بتيجي من لوحة التحكم

// Helper functions
function getProductById(id) {
  return PRODUCTS.find(p => p.id === parseInt(id));
}

function getProductBySlug(slug) {
  return PRODUCTS.find(p => p.slug === slug);
}

function getProductsByCategory(categoryId) {
  return PRODUCTS.filter(p => p.category === categoryId);
}

function getBestSellers(limit = 8) {
  return PRODUCTS.filter(p => p.isBestSeller).slice(0, limit);
}

function getNewArrivals(limit = 8) {
  return PRODUCTS.filter(p => p.isNew).slice(0, limit);
}

function getFeatured(limit = 8) {
  return PRODUCTS.filter(p => p.isFeatured).slice(0, limit);
}

function getOffers(limit = 20) {
  return PRODUCTS.filter(p => p.isOffer || p.discount > 0).slice(0, limit);
}

function getSimilarProducts(product, limit = 4) {
  return PRODUCTS.filter(p => p.category === product.category && p.id !== product.id).slice(0, limit);
}

function searchProducts(query) {
  const q = query.toLowerCase().trim();
  return PRODUCTS.filter(p => {
    const catAr = CATEGORIES.find(c => c.id === p.category)?.name || '';
    return p.name.toLowerCase().includes(q) ||
      p.shortDesc.toLowerCase().includes(q) ||
      p.tags.some(t => t.toLowerCase().includes(q)) ||
      p.category.toLowerCase().includes(q) ||
      catAr.includes(q);
  });
}
