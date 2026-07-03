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

const PRODUCTS = [

  // ── أواني الطهي ───────────────────────────────────────────────────
  {
    id: 1,
    name: 'طقم حلل ستانلس ستيل 7 قطع بكاشات',
    slug: 'taqm-hlal-stainless-7-qta3',
    category: 'cookware', subcategory: 'Cooking Sets',
    price: 1250, oldPrice: 1700, discount: 26,
    rating: 4.8, reviewCount: 215,
    image: img('1580929753603-10519c6e480a'),
    images: [img('1580929753603-10519c6e480a'), img('1556909114-f6e7ad7d3136')],
    shortDesc: 'طقم حلل ستانلس ستيل 18/10 مع غطاء زجاج (كاشة) — 7 قطع بأحجام مختلفة.',
    description: 'طقم حلل ستانلس ستيل متكامل من 7 قطع بأحجام متنوعة تناسب كل احتياجات الطهي. الغطاء زجاجي (كاشة) مقاوم للحرارة يتيح مراقبة الطعام أثناء الطهي. القاع ثلاثي الطبقات يضمن توزيعاً متساوياً للحرارة. مناسب لجميع أنواع البوتاجازات بما فيها الحث الحراري.',
    material: 'ستانلس ستيل 18/10', size: 'طقم 7 قطع', color: 'فضي', pieces: 7,
    inStock: true, isNew: false, isBestSeller: true, isFeatured: true, isOffer: true,
    tags: ['حلل', 'طقم حلل', 'ستانلس', 'كاشة', 'كاشات'],
    features: ['ستانلس ستيل 18/10 عالي الجودة', 'غطاء زجاجي مقاوم للحرارة', 'قاع ثلاثي الطبقات', 'مناسب للحث الحراري', 'مقابض مقاومة للحرارة'],
    specs: { 'عدد القطع': '7', 'الخامة': 'ستانلس ستيل 18/10', 'الأحجام': '16/18/20/22/24 سم', 'آمن للغسالة': 'نعم', 'الحث الحراري': 'نعم' },
    reviews: [
      { name: 'أم أحمد', rating: 5, date: '2025-03-10', comment: 'طقم ممتاز جداً، الحلل متينة والكاشات محكمة. اشتريته من شهرين وزي الجديد.' },
      { name: 'سارة محمود', rating: 5, date: '2025-02-15', comment: 'جودة رائعة وسعر مناسب. أنصح به بشدة.' },
    ],
  },
  {
    id: 2,
    name: 'طقم حلل جرانيت غير لاصق 9 قطع',
    slug: 'taqm-hlal-granite-9-qta3',
    category: 'cookware', subcategory: 'Granite',
    price: 1650, oldPrice: 2200, discount: 25,
    rating: 4.7, reviewCount: 189,
    image: '/assets/118c63e8d5e6ecfed5deb6abe17d18a8.jpg',
    images: ['/assets/118c63e8d5e6ecfed5deb6abe17d18a8.jpg', img('1556909114-f6e7ad7d3136')],
    shortDesc: 'طقم حلل جرانيت 9 قطع بطلاء غير لاصق ثلاثي الطبقات خالٍ من PFOA.',
    description: 'طقم حلل جرانيت فاخر من 9 قطع بطلاء جرانيت ثلاثي الطبقات غير لاصق. الطعام لا يعلق أبداً وسهل التنظيف. المقابض باردة اللمس ومريحة.',
    material: 'ألومنيوم بطلاء جرانيت', size: 'طقم 9 قطع', color: 'رمادي داكن', pieces: 9,
    inStock: true, isNew: true, isBestSeller: false, isFeatured: true, isOffer: true,
    tags: ['حلل', 'جرانيت', 'غير لاصق', 'طقم حلل'],
    features: ['طلاء جرانيت ثلاثي الطبقات', 'خالٍ من PFOA', 'مناسب للحث الحراري', 'سهل التنظيف', 'مقابض باردة اللمس'],
    specs: { 'عدد القطع': '9', 'الخامة': 'ألومنيوم + جرانيت', 'الطلاء': 'جرانيت ثلاثي الطبقات', 'الحث الحراري': 'نعم' },
    reviews: [
      { name: 'منى علي', rating: 5, date: '2025-04-01', comment: 'اللون جميل والحلل متينة. الطعام ما بيعلقش خالص.' },
    ],
  },

  // ── مقالي وصواني ──────────────────────────────────────────────────
  {
    id: 3,
    name: 'مقلاة تفلون عميقة 28 سم',
    slug: 'maqla-teflon-28cm',
    category: 'pans', subcategory: 'Non-Stick Pans',
    price: 320, oldPrice: 420, discount: 24,
    rating: 4.6, reviewCount: 385,
    image: img('1592156328697-079f6ee0cfa5'),
    images: [img('1592156328697-079f6ee0cfa5')],
    shortDesc: 'مقلاة تفلون عميقة 28 سم بطلاء غير لاصق مقاوم للخدش مع غطاء زجاجي.',
    description: 'مقلاة تفلون عميقة 28 سم مثالية للقلي والطهي اليومي. الطلاء غير اللاصق يمنع التصاق الطعام تماماً. العمق المناسب يجعلها مثالية لطهي الصلصات والقلي. المقبض مريح ومقاوم للحرارة.',
    material: 'ألومنيوم بطلاء تفلون', size: '28 سم', color: 'أسود', pieces: 1,
    inStock: true, isNew: false, isBestSeller: true, isFeatured: false, isOffer: true,
    tags: ['مقلاة', 'تفلون', 'غير لاصق', '28 سم'],
    features: ['طلاء تفلون غير لاصق', 'مقاوم للخدش', 'غطاء زجاجي مرفق', 'مقبض مريح بارد اللمس', 'مناسبة للحث الحراري'],
    specs: { 'القطر': '28 سم', 'العمق': '6 سم', 'الخامة': 'ألومنيوم', 'الطلاء': 'تفلون' },
    reviews: [
      { name: 'ليلى أحمد', rating: 5, date: '2025-04-10', comment: 'البيض بيطلع من غيرما يعلق. أحسن مقلاة اشتريتها.' },
    ],
  },
  {
    id: 4,
    name: 'مقلاة غريل حديد زهر 26 سم',
    slug: 'maqla-grill-cast-iron-26cm',
    category: 'pans', subcategory: 'Grill Pans',
    price: 580, oldPrice: 750, discount: 23,
    rating: 4.7, reviewCount: 134,
    image: img('1716488286931-79cef654e08c'),
    images: [img('1716488286931-79cef654e08c')],
    shortDesc: 'مقلاة غريل حديد زهر 26 سم للحصول على خطوط الشواء في المنزل.',
    description: 'مقلاة غريل من الحديد الزهر المعالج مسبقاً. الأخاديد المرتفعة تترك خطوط الشواء الجميلة على اللحوم والدجاج والخضروات. الحديد الزهر يحتفظ بالحرارة لفترة طويلة.',
    material: 'حديد زهر معالج مسبقاً', size: '26 سم', color: 'أسود', pieces: 1,
    inStock: true, isNew: false, isBestSeller: false, isFeatured: true, isOffer: false,
    tags: ['مقلاة غريل', 'حديد زهر', 'شواء', 'غريل'],
    features: ['حديد زهر معالج مسبقاً', 'أخاديد شواء مرتفعة', 'يحتفظ بالحرارة', 'مناسب للفرن', 'يدوم مدى الحياة'],
    specs: { 'القطر': '26 سم', 'الخامة': 'حديد زهر', 'الوزن': '2.3 كجم' },
    reviews: [
      { name: 'طارق محمد', rating: 5, date: '2025-02-05', comment: 'خطوط الشواء بتطلع رائعة على اللحمة. يستاهل كل جنيه.' },
    ],
  },

  // ── قدور وأوعية ────────────────────────────────────────────────────
  {
    id: 5,
    name: 'قدر شوربة كبير ستانلس 10 لتر',
    slug: 'qedr-shorba-stainless-10ltr',
    category: 'pots', subcategory: 'Stock Pots',
    price: 620, oldPrice: 820, discount: 24,
    rating: 4.7, reviewCount: 167,
    image: img('1556909114-f6e7ad7d3136'),
    images: [img('1556909114-f6e7ad7d3136'), '/assets/118c63e8d5e6ecfed5deb6abe17d18a8.jpg'],
    shortDesc: 'قدر شوربة ستانلس ستيل 10 لتر — مثالي للعائلات الكبيرة والمناسبات.',
    description: 'قدر شوربة كبير من الستانلس ستيل سعة 10 لتر. مثالي لطهي الشوربة والمكرونة والأرز. القاع المغلف يضمن التوزيع المتساوي للحرارة.',
    material: 'ستانلس ستيل 18/10', size: '10 لتر', color: 'فضي', pieces: 1,
    inStock: true, isNew: false, isBestSeller: true, isFeatured: false, isOffer: true,
    tags: ['قدر', 'شوربة', 'ستانلس', '10 لتر', 'قدر كبير'],
    features: ['سعة 10 لتر', 'قاع مغلف ثلاثي الطبقات', 'مقياس داخلي', 'غطاء زجاجي', 'مناسب للحث الحراري'],
    specs: { 'السعة': '10 لتر', 'الخامة': 'ستانلس ستيل 18/10', 'الوزن': '2.8 كجم' },
    reviews: [
      { name: 'ياسمين بدر', rating: 5, date: '2025-03-15', comment: 'قدر تقيل ومتين. بيتحمل الطهي اليومي بكل سهولة.' },
    ],
  },
  {
    id: 6,
    name: 'قدر ضغط ستانلس ستيل 6 لتر',
    slug: 'qedr-daqht-stainless-6-ltr',
    category: 'pots', subcategory: 'Sauce Pots',
    price: 750, oldPrice: null, discount: 0,
    rating: 4.9, reviewCount: 312,
    image: '/assets/118c63e8d5e6ecfed5deb6abe17d18a8.jpg',
    images: ['/assets/118c63e8d5e6ecfed5deb6abe17d18a8.jpg', img('1556909114-f6e7ad7d3136')],
    shortDesc: 'قدر ضغط ستانلس ستيل 6 لتر بنظام أمان متعدد — يسرع الطهي 70%.',
    description: 'قدر ضغط ستانلس ستيل مع نظام أمان متعدد المراحل. يطهو الأرز والفاصوليا والفراخ في وقت قياسي. السعة 6 لتر مناسبة لعائلة من 4-6 أشخاص.',
    material: 'ستانلس ستيل 18/10', size: '6 لتر', color: 'فضي', pieces: 1,
    inStock: true, isNew: false, isBestSeller: true, isFeatured: false, isOffer: false,
    tags: ['قدر ضغط', 'طهي سريع', 'ستانلس'],
    features: ['يسرع الطهي بنسبة 70%', 'نظام أمان ثلاثي', 'صمام تنفيس تلقائي', 'مناسب للحث الحراري', 'ضمان 5 سنوات'],
    specs: { 'السعة': '6 لتر', 'الخامة': 'ستانلس ستيل 18/10', 'صمامات الأمان': '3', 'الوزن': '2.1 كجم' },
    reviews: [
      { name: 'هبة الله', rating: 5, date: '2025-02-14', comment: 'الأرز بيتطهى في 5 دقايق! أفضل شراء عملته.' },
    ],
  },

  // ── سكاكين وأدوات تقطيع ────────────────────────────────────────────
  {
    id: 7,
    name: 'طقم سكاكين مطبخ 6 قطع مع حامل خشب',
    slug: 'taqm-sakakeen-6-qta3',
    category: 'cutlery', subcategory: 'Knife Sets',
    price: 920, oldPrice: 1200, discount: 23,
    rating: 4.9, reviewCount: 243,
    image: '/assets/23817701f93272599b47a8f6b169392c.jpg',
    images: ['/assets/23817701f93272599b47a8f6b169392c.jpg', '/assets/23817701f93272599b47a8f6b169392c.jpg'],
    shortDesc: 'طقم سكاكين مطبخ احترافي 6 قطع من الفولاذ الألماني مع حامل خشب أكاسيا.',
    description: 'طقم سكاكين مطبخ احترافي من 6 قطع مصنوعة من الفولاذ الألماني عالي الكربون. يشمل: سكينة شيف، سكينة خبز، سكينة تقطيع، سكينة بارينج، ومقص مطبخ. مع حامل خشب أكاسيا فاخر.',
    material: 'فولاذ ألماني عالي الكربون', size: 'طقم 6 قطع', color: 'فضي/بني', pieces: 6,
    inStock: true, isNew: false, isBestSeller: true, isFeatured: true, isOffer: true,
    tags: ['سكاكين', 'طقم سكاكين', 'سكاكين مطبخ', 'سكينة'],
    features: ['فولاذ ألماني عالي الكربون', 'حافة مشحوذة يدوياً', 'مقابض مريحة', 'حامل خشب أكاسيا', 'مقاومة للصدأ'],
    specs: { 'عدد القطع': '6 + حامل', 'الخامة': 'فولاذ ألماني', 'قياس سكينة الشيف': '20 سم', 'آمن للغسالة': 'لا (يُنصح بالغسيل اليدوي)' },
    reviews: [
      { name: 'سامر دياب', rating: 5, date: '2025-04-05', comment: 'سكاكين حادة جداً وراحة في الإمساك. أفضل طقم اشتريته.' },
    ],
  },
  {
    id: 8,
    name: 'لوح تقطيع خشب أكاسيا كبير',
    slug: 'loh-taqtee3-khashab-akasia',
    category: 'cutlery', subcategory: 'Knife Blocks',
    price: 180, oldPrice: 240, discount: 25,
    rating: 4.6, reviewCount: 156,
    image: '/assets/23817701f93272599b47a8f6b169392c.jpg',
    images: ['/assets/23817701f93272599b47a8f6b169392c.jpg', img('1587132117816-061b35073a4e')],
    shortDesc: 'لوح تقطيع خشب أكاسيا طبيعي 38×28 سم مع بعبوص جانبي لتجميع العصائر.',
    description: 'لوح تقطيع من خشب الأكاسيا الطبيعي المضاد للبكتيريا. البعبوص الجانبي يجمع العصائر. المظهر الطبيعي الجميل يضيف أناقة لمطبخك.',
    material: 'خشب أكاسيا طبيعي', size: '38 × 28 سم', color: 'خشبي طبيعي', pieces: 1,
    inStock: true, isNew: false, isBestSeller: false, isFeatured: false, isOffer: false,
    tags: ['لوح تقطيع', 'خشب', 'أكاسيا', 'تقطيع'],
    features: ['خشب أكاسيا طبيعي', 'مضاد للبكتيريا طبيعياً', 'يحافظ على حدة السكاكين', 'بعبوص للعصائر', 'أرجل مطاطية مانعة للانزلاق'],
    specs: { 'المقاس': '38 × 28 سم', 'الخامة': 'خشب أكاسيا', 'العناية': 'غسيل يدوي وتزييت دوري' },
    reviews: [],
  },

  // ── ملاعق وشوك ─────────────────────────────────────────────────────
  {
    id: 9,
    name: 'طقم أدوات مائدة ستانلس 24 قطعة (6 أشخاص)',
    slug: 'taqm-adawat-maeda-24-qta3',
    category: 'spoons-forks', subcategory: 'Cutlery Sets',
    price: 420, oldPrice: 580, discount: 28,
    rating: 4.7, reviewCount: 289,
    image: '/assets/43ba30bd50f05189d0b4702a6038bee8.jpg',
    images: ['/assets/43ba30bd50f05189d0b4702a6038bee8.jpg', '/assets/995e332af4d7a8f3cbd05c27e31da1e8.jpg'],
    shortDesc: 'طقم أدوات مائدة ستانلس ستيل 24 قطعة لـ 6 أشخاص — شوك وملاعق وسكاكين.',
    description: 'طقم أدوات مائدة أنيق من الستانلس ستيل 18/10 لـ 6 أشخاص. يشمل: 6 شوك عشاء، 6 سكاكين، 6 ملاعق شوربة، 6 ملاعق حلويات. التشطيب اللامع يضفي لمسة راقية على مائدتك.',
    material: 'ستانلس ستيل 18/10', size: '24 قطعة (6 أشخاص)', color: 'فضي لامع', pieces: 24,
    inStock: true, isNew: false, isBestSeller: true, isFeatured: false, isOffer: true,
    tags: ['ملاعق', 'شوك', 'أدوات مائدة', 'طقم ستانلس'],
    features: ['ستانلس ستيل 18/10', 'تشطيب لامع', 'آمن للغسالة', 'مقاوم للصدأ', 'يأتي في علبة هدية'],
    specs: { 'عدد القطع': '24', 'يكفي لـ': '6 أشخاص', 'الخامة': 'ستانلس ستيل 18/10', 'آمن للغسالة': 'نعم' },
    reviews: [
      { name: 'إيناس مصطفى', rating: 5, date: '2025-03-22', comment: 'أدوات أنيقة جداً. استخدمتها في حفلة وكل الناس سألوا عنها.' },
    ],
  },

  // ── أطقم المائدة ────────────────────────────────────────────────────
  {
    id: 10,
    name: 'طقم عشاء بورسلين أبيض 18 قطعة',
    slug: 'taqm-3asha-porcelain-abyad-18',
    category: 'dinner-sets', subcategory: 'Porcelain Sets',
    price: 750, oldPrice: 950, discount: 21,
    rating: 4.8, reviewCount: 218,
    image: img('1681412204696-c1a3e0c51f8d'),
    images: [img('1681412204696-c1a3e0c51f8d')],
    shortDesc: 'طقم عشاء بورسلين أبيض 18 قطعة لـ 6 أشخاص — كلاسيكي وأنيق.',
    description: 'طقم عشاء بورسلين أبيض فاخر من 18 قطعة لـ 6 أشخاص. يشمل: 6 أطباق كبيرة، 6 أطباق سلطة، 6 أطباق شوربة. مناسب لكل ديكور ومناسبة.',
    material: 'بورسلين فاخر', size: '18 قطعة (6 أشخاص)', color: 'أبيض', pieces: 18,
    inStock: true, isNew: false, isBestSeller: true, isFeatured: true, isOffer: true,
    tags: ['طقم أطباق', 'بورسلين', 'أبيض', 'عشاء', 'أطباق'],
    features: ['بورسلين عالي الجودة', 'آمن للميكروويف', 'آمن للغسالة', 'مقاوم للخدش', 'تصميم كلاسيكي'],
    specs: { 'عدد القطع': '18', 'الخامة': 'بورسلين', 'آمن للميكروويف': 'نعم' },
    reviews: [
      { name: 'ريم عبدالله', rating: 5, date: '2025-04-12', comment: 'طقم جميل جداً وجودة ممتازة. ضيوفي دايماً بيمدحوه.' },
    ],
  },
  {
    id: 11,
    name: 'طقم عشاء سيراميك ملون 18 قطعة',
    slug: 'taqm-3asha-ceramic-mulawan-18',
    category: 'dinner-sets', subcategory: 'Stoneware Sets',
    price: 890, oldPrice: 1100, discount: 19,
    rating: 4.7, reviewCount: 124,
    image: '/assets/115eb3f52fa60cb76cd3660f212984f2.jpg',
    images: ['/assets/115eb3f52fa60cb76cd3660f212984f2.jpg'],
    shortDesc: 'طقم عشاء سيراميك بألوان حديثة 18 قطعة — تصميم عصري أنيق.',
    description: 'طقم عشاء سيراميك بألوان عصرية مميزة من 18 قطعة. الألوان الزاهية تضيف حيوية لمائدتك. مناسب للاستخدام اليومي والمناسبات.',
    material: 'سيراميك', size: '18 قطعة (6 أشخاص)', color: 'أزرق داكن', pieces: 18,
    inStock: true, isNew: true, isBestSeller: false, isFeatured: true, isOffer: false,
    tags: ['طقم أطباق', 'سيراميك', 'ملون', 'عشاء'],
    features: ['سيراميك عالي الجودة', 'آمن للميكروويف والفرن', 'آمن للغسالة', 'مقاوم للخدش', 'ألوان ثابتة'],
    specs: { 'عدد القطع': '18', 'الخامة': 'سيراميك', 'آمن للفرن': 'حتى 200 درجة', 'آمن للميكروويف': 'نعم' },
    reviews: [
      { name: 'ليلى جمال', rating: 5, date: '2025-03-08', comment: 'جميل جداً! اللون أعمق وأجمل من الصور.' },
    ],
  },

  // ── أدوات المطبخ ───────────────────────────────────────────────────
  {
    id: 12,
    name: 'طقم أدوات مطبخ سيليكون 8 قطع',
    slug: 'taqm-adawat-silicone-8-qta3',
    category: 'kitchen-tools', subcategory: 'Spatulas',
    price: 260, oldPrice: 340, discount: 24,
    rating: 4.6, reviewCount: 345,
    image: img('1716051170366-31998e5b4d54'),
    images: [img('1716051170366-31998e5b4d54')],
    shortDesc: 'طقم أدوات مطبخ سيليكون 8 قطع مقاوم للحرارة حتى 230 درجة — لا يخدش الحلل.',
    description: 'طقم أدوات مطبخ من السيليكون الغذائي عالي الجودة من 8 قطع. مقاوم للحرارة حتى 230 درجة ولا يخدش أواني الطهي.',
    material: 'سيليكون غذائي + قلب ستانلس', size: 'طقم 8 قطع', color: 'أسود/فضي', pieces: 8,
    inStock: true, isNew: false, isBestSeller: true, isFeatured: false, isOffer: true,
    tags: ['أدوات مطبخ', 'سيليكون', 'ملعقة', 'مقشطة'],
    features: ['مقاوم للحرارة حتى 230 درجة', 'سيليكون غذائي خالٍ من BPA', 'لا يخدش الحلل', 'آمن للغسالة', 'مقابض مريحة'],
    specs: { 'عدد القطع': '8', 'الخامة': 'سيليكون + ستانلس', 'مقاومة الحرارة': '230 درجة', 'آمن للغسالة': 'نعم' },
    reviews: [
      { name: 'نادية سليم', rating: 5, date: '2025-04-18', comment: 'أدوات ممتازة والسيليكون تقيل. ما بيخدشش الحلل خالص.' },
    ],
  },
  {
    id: 13,
    name: 'مبشرة ستانلس ستيل 4 وجوه',
    slug: 'mabshara-stainless-4-woguh',
    category: 'kitchen-tools', subcategory: 'Graters',
    price: 155, oldPrice: 200, discount: 23,
    rating: 4.4, reviewCount: 143,
    image: '/assets/5f01eed847ae4e6dd8390cd177bcd7be.jpg',
    images: ['/assets/5f01eed847ae4e6dd8390cd177bcd7be.jpg'],
    shortDesc: 'مبشرة ستانلس ستيل 4 وجوه للجبن والخضروات والفواكه — قاعدة مانعة للانزلاق.',
    description: 'مبشرة ستانلس ستيل بـ 4 وجوه مختلفة مناسبة للجبن والجزر والبطاطس وشرائح الخضروات. قاعدة مطاطية مانعة للانزلاق.',
    material: 'ستانلس ستيل', size: '28 سم', color: 'فضي', pieces: 1,
    inStock: true, isNew: false, isBestSeller: false, isFeatured: false, isOffer: false,
    tags: ['مبشرة', 'مبشرة ستانلس', 'أدوات مطبخ'],
    features: ['4 وجوه للتبشير', 'قاعدة مانعة للانزلاق', 'مقبض مريح', 'صينية تجميع', 'آمن للغسالة'],
    specs: { 'الوجوه': '4', 'الخامة': 'ستانلس ستيل', 'الارتفاع': '28 سم' },
    reviews: [],
  },

  // ── تخزين وتنظيم ────────────────────────────────────────────────────
  {
    id: 14,
    name: 'طقم حافظات طعام زجاج 10 قطع',
    slug: 'taqm-hafezat-zogag-10-qta3',
    category: 'storage', subcategory: 'Food Containers',
    price: 580, oldPrice: 770, discount: 25,
    rating: 4.8, reviewCount: 253,
    image: '/assets/bfb9f54abed54dcd365952045d0236aa.jpg',
    images: ['/assets/bfb9f54abed54dcd365952045d0236aa.jpg'],
    shortDesc: 'طقم حافظات طعام زجاج بوروسيليكات 10 قطع — من الثلاجة للفرن مباشرة.',
    description: 'طقم حافظات طعام من الزجاج البوروسيليكات المقاوم للحرارة. مناسبة من الثلاجة للفرن للميكروويف مباشرة. الأغطية المحكمة تحافظ على طازجية الطعام.',
    material: 'زجاج بوروسيليكات + أغطية PP', size: 'طقم 10 قطع', color: 'شفاف/أزرق', pieces: 10,
    inStock: true, isNew: false, isBestSeller: true, isFeatured: false, isOffer: true,
    tags: ['حافظات طعام', 'زجاج', 'بوروسيليكات', 'تخزين'],
    features: ['زجاج بوروسيليكات مقاوم للصدمات', 'أغطية محكمة الإغلاق', 'آمن للفرن والميكروويف', 'آمن للغسالة', 'تصميم قابل للتداخل'],
    specs: { 'عدد القطع': '10', 'الخامة': 'زجاج بوروسيليكات', 'آمن للفرن': 'حتى 400 درجة' },
    reviews: [
      { name: 'رشا كمال', rating: 5, date: '2025-04-15', comment: 'استبدلت كل علب البلاستيك بيها. جودة ممتازة!' },
    ],
  },

  // ── أدوات الخبز ────────────────────────────────────────────────────
  {
    id: 15,
    name: 'طقم صواني خبز غير لاصق 5 قطع',
    slug: 'taqm-sawani-khubz-5-qta3',
    category: 'baking', subcategory: 'Baking Trays',
    price: 470, oldPrice: 620, discount: 24,
    rating: 4.7, reviewCount: 178,
    image: '/assets/da7377d2b8808d7f2b2d23c88780fce0.jpg',
    images: ['/assets/da7377d2b8808d7f2b2d23c88780fce0.jpg'],
    shortDesc: 'طقم صواني خبز 5 قطع بطلاء غير لاصق للكيك والبسكويت والمعجنات.',
    description: 'طقم صواني خبز متكامل من 5 قطع. يشمل: صينية كبيرة، قالب كيك مربع، قالب كيك دائري، قالب تورتة، صينية كب كيك. الحديد الكربوني يضمن تحمير متساوٍ.',
    material: 'حديد كربوني بطلاء غير لاصق', size: 'طقم 5 قطع', color: 'رمادي داكن', pieces: 5,
    inStock: true, isNew: false, isBestSeller: true, isFeatured: false, isOffer: true,
    tags: ['صواني خبز', 'كيك', 'خبز', 'معجنات', 'بسكويت'],
    features: ['طلاء غير لاصق داخلي وخارجي', 'حديد كربوني سميك', 'تحمير متساوٍ', 'مقاوم للانحناء', 'آمن للغسالة'],
    specs: { 'عدد القطع': '5', 'الخامة': 'حديد كربوني', 'الطلاء': 'غير لاصق', 'آمن للفرن': 'حتى 230 درجة' },
    reviews: [
      { name: 'سونيا محمود', rating: 5, date: '2025-03-25', comment: 'الكيك بيطلع مظبوط كل مرة. طقم ممتاز.' },
    ],
  },
  {
    id: 16,
    name: 'طقم أوعية خلط ستانلس 5 قطع',
    slug: 'taqm-aw3ia-khalt-5-qta3',
    category: 'baking', subcategory: 'Mixing Bowls',
    price: 310, oldPrice: 410, discount: 24,
    rating: 4.6, reviewCount: 195,
    image: '/assets/c964a7a84f496f11a630cf20a09c30e3.jpg',
    images: ['/assets/c964a7a84f496f11a630cf20a09c30e3.jpg'],
    shortDesc: 'طقم أوعية خلط ستانلس 5 أحجام — للعجن والخلط والتحضير.',
    description: 'طقم أوعية خلط من الستانلس ستيل 18/8 من 5 أحجام. القاعدة المطاطية تمنع الانزلاق والفوهة الجانبية تسهل الصب.',
    material: 'ستانلس ستيل 18/8', size: 'طقم 5 قطع (1–5 لتر)', color: 'فضي', pieces: 5,
    inStock: true, isNew: false, isBestSeller: true, isFeatured: false, isOffer: false,
    tags: ['أوعية خلط', 'خلط', 'عجن', 'ستانلس'],
    features: ['5 أحجام متداخلة', 'قاعدة مطاطية', 'فوهة صب للأكبر', 'آمن للغسالة', 'آمن للفرن'],
    specs: { 'عدد القطع': '5', 'الأحجام': '1 / 1.5 / 2 / 3 / 5 لتر', 'الخامة': 'ستانلس 18/8' },
    reviews: [
      { name: 'رنا طارق', rating: 5, date: '2025-02-10', comment: 'متينة ومريحة. التداخل يوفر مساحة التخزين.' },
    ],
  },

  // ── أدوات التقديم ───────────────────────────────────────────────────
  {
    id: 17,
    name: 'طبق تقديم رخام بمقابض 35 سم',
    slug: 'tabaq-taqdeem-rokham',
    category: 'serving', subcategory: 'Serving Platters',
    price: 360, oldPrice: 460, discount: 22,
    rating: 4.8, reviewCount: 98,
    image: img('1716104016459-391b712bcddc'),
    images: [img('1716104016459-391b712bcddc')],
    shortDesc: 'طبق تقديم رخام أبيض طبيعي 35×20 سم بمقابض — للجبن والمزه والحلويات.',
    description: 'طبق تقديم من الرخام الأبيض الطبيعي بمقابض مصقولة. السطح البارد مثالي للجبن والمقبلات. كل طبق فريد بعروقه الطبيعية المختلفة.',
    material: 'رخام أبيض طبيعي', size: '35 × 20 سم', color: 'أبيض/عروق رمادية', pieces: 1,
    inStock: true, isNew: false, isBestSeller: false, isFeatured: true, isOffer: true,
    tags: ['طبق تقديم', 'رخام', 'مزه', 'جبن', 'تقديم'],
    features: ['رخام طبيعي أصيل', 'مقابض مصقولة', 'أرجل مطاطية', 'سطح بارد طبيعي', 'كل قطعة فريدة'],
    specs: { 'الخامة': 'رخام طبيعي', 'المقاس': '35 × 20 سم', 'السماكة': '1.5 سم', 'الوزن': '1.8 كجم' },
    reviews: [
      { name: 'سيلين بدر', rating: 5, date: '2025-04-02', comment: 'الشكل فخم جداً. كل الضيوف بيعجبهم.' },
    ],
  },

  // ── أواني زجاجية ────────────────────────────────────────────────────
  {
    id: 18,
    name: 'طقم كؤوس بلور 6 قطع (350 مل)',
    slug: 'taqm-kuus-bolour-6-qta3',
    category: 'glassware', subcategory: 'Drinking Glasses',
    price: 260, oldPrice: 340, discount: 24,
    rating: 4.7, reviewCount: 187,
    image: '/assets/f2d5da39892babad1d2902a95dbe1b62.jpg',
    images: ['/assets/f2d5da39892babad1d2902a95dbe1b62.jpg'],
    shortDesc: 'طقم كؤوس بلور خالٍ من الرصاص 6 قطع 350 مل — للمياه والعصائر.',
    description: 'طقم كؤوس بلور فاخر خالٍ من الرصاص من 6 قطع. الشفافية العالية تجعل أي مشروب يبدو أجمل. الحواف الرفيعة توفر تجربة شرب مريحة.',
    material: 'بلور خالٍ من الرصاص', size: '350 مل × 6', color: 'شفاف', pieces: 6,
    inStock: true, isNew: false, isBestSeller: true, isFeatured: false, isOffer: true,
    tags: ['كؤوس', 'بلور', 'كؤوس مياه', 'عصير'],
    features: ['بلور خالٍ من الرصاص', 'آمن للغسالة', 'حواف مقاومة للكسر', 'شفافية عالية', 'تصميم أنيق'],
    specs: { 'عدد القطع': '6', 'السعة': '350 مل', 'الخامة': 'بلور', 'الارتفاع': '14 سم' },
    reviews: [
      { name: 'نادية لطفي', rating: 5, date: '2025-03-18', comment: 'كؤوس أنيقة جداً تبان زي كؤوس المطاعم الكبيرة.' },
    ],
  },
  {
    id: 19,
    name: 'أكواب زجاج مزدوج الجدار 4 قطع',
    slug: 'akwab-zogag-mozdawaj-4-qta3',
    category: 'glassware', subcategory: 'Mugs',
    price: 310, oldPrice: null, discount: 0,
    rating: 4.8, reviewCount: 144,
    image: img('1514228879-e5b04b8dea4f'),
    images: [img('1514228879-e5b04b8dea4f')],
    shortDesc: 'أكواب زجاج مزدوجة الجدار 4 قطع 350 مل — الشاي والقهوة تعوم بداخلها.',
    description: 'أكواب زجاج مزدوجة الجدار تخلق تأثيراً بصرياً مذهلاً. الجدار المزدوج يحافظ على الحرارة ويجعل الخارج بارداً للمس.',
    material: 'زجاج بوروسيليكات مزدوج الجدار', size: '350 مل × 4', color: 'شفاف', pieces: 4,
    inStock: true, isNew: true, isBestSeller: false, isFeatured: true, isOffer: false,
    tags: ['أكواب زجاج', 'مزدوج الجدار', 'قهوة', 'شاي'],
    features: ['زجاج مزدوج الجدار', 'بوروسيليكات عالي الجودة', 'خارجه بارد للمس', 'آمن للغسالة', 'تأثير بصري مذهل'],
    specs: { 'عدد القطع': '4', 'السعة': '350 مل', 'الخامة': 'بوروسيليكات', 'الجدار': 'مزدوج' },
    reviews: [
      { name: 'عمر يوسف', rating: 5, date: '2025-04-20', comment: 'الشاي بيبان عايم جوا. جميل جداً.' },
    ],
  },

  // ── إكسسوارات المطبخ ──────────────────────────────────────────────
  {
    id: 20,
    name: 'براد شاي تركي ستانلس 2 قطعة',
    slug: 'brad-shay-torky-stainless',
    category: 'accessories', subcategory: 'Thermometers',
    price: 380, oldPrice: 490, discount: 22,
    rating: 4.9, reviewCount: 156,
    image: img('1632465216582-632d35a2d71f'),
    images: [img('1632465216582-632d35a2d71f')],
    shortDesc: 'براد شاي تركي ستانلس ستيل 2 قطعة (1.5 + 3 لتر) — ينضج الشاي في أحسن حالاته.',
    description: 'براد شاي تركي من الستانلس ستيل من قطعتين. القدر السفلي 3 لتر للماء والقدر العلوي 1.5 لتر لتركيز الشاي. يعمل على جميع أنواع البوتاجازات.',
    material: 'ستانلس ستيل 18/10', size: '1.5 لتر + 3 لتر', color: 'فضي', pieces: 2,
    inStock: true, isNew: false, isBestSeller: true, isFeatured: false, isOffer: true,
    tags: ['براد شاي', 'شاي تركي', 'ستانلس'],
    features: ['ستانلس ستيل 18/10', 'قطعتان متداخلتان', 'يعمل على الحث الحراري', 'فوهة صب ممتازة', 'مقابض مريحة'],
    specs: { 'عدد القطع': '2', 'العلوي': '1.5 لتر', 'السفلي': '3 لتر', 'الحث الحراري': 'نعم' },
    reviews: [
      { name: 'فاتن حداد', rating: 5, date: '2025-02-25', comment: 'أحسن براد شاي. الشاي بيطلع بالضبط زي ما بنحب.' },
    ],
  },
  {
    id: 21,
    name: 'ميزان مطبخ رقمي 5 كجم',
    slug: 'mezan-matbakh-raqami-5kg',
    category: 'accessories', subcategory: 'Scales',
    price: 155, oldPrice: 200, discount: 23,
    rating: 4.7, reviewCount: 298,
    image: img('1721522922185-f665934b902b'),
    images: [img('1721522922185-f665934b902b')],
    shortDesc: 'ميزان مطبخ رقمي 5 كجم بدقة 1 جرام — للخبز والطهي والحمية.',
    description: 'ميزان مطبخ رقمي دقيق بسعة 5 كجم ودقة 1 جرام. شاشة LCD كبيرة مضيئة. وظيفة الصفر (Tare) تسمح بقياس دقيق بدون وزن الوعاء.',
    material: 'ABS + صفيحة ستانلس', size: 'سعة 5 كجم', color: 'أبيض', pieces: 1,
    inStock: true, isNew: false, isBestSeller: true, isFeatured: false, isOffer: false,
    tags: ['ميزان', 'ميزان رقمي', 'ميزان مطبخ', 'وزن'],
    features: ['دقة 1 جرام', 'وظيفة الصفر', 'شاشة LCD مضيئة', 'إيقاف تلقائي', 'بطارية مرفقة'],
    specs: { 'السعة': '5 كجم', 'الدقة': '1 جرام', 'الوحدات': 'جرام / أونصة / كجم', 'الشاشة': 'LCD مضيئة' },
    reviews: [
      { name: 'خالد رضا', rating: 5, date: '2025-03-08', comment: 'دقيق جداً وسهل الاستخدام. مثالي للخبز.' },
    ],
  },
];

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
