// ============================================================
//  js/checkout.js  —  Checkout page logic + WhatsApp order
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
  const items = CART.getItems();

  if (items.length === 0) {
    document.getElementById('emptyCartMsg').style.display = 'block';
    document.getElementById('checkoutForm').style.display = 'none';
    return;
  }

  // Set InstaPay details
  const shippingFee = CONFIG.shippingFee;
  document.getElementById('shippingFeeLabel').textContent = formatPrice(shippingFee);
  document.getElementById('instaPayAmount').textContent = formatPrice(shippingFee);
  document.getElementById('instaPayPhone').textContent = CONFIG.instaPayNumber;
  document.getElementById('confirmFeeLabel').textContent = formatPrice(shippingFee);
  document.getElementById('confirmPhoneLabel').textContent = CONFIG.instaPayNumber;

  // Delivery method toggle
  const radioDelivery = document.querySelector('input[value="delivery"]');
  const radioPickup = document.querySelector('input[value="pickup"]');
  const instaPayCard = document.getElementById('instaPayCard');
  const shippingRow = document.getElementById('shippingRow');

  function updateDeliveryMethod() {
    const isDelivery = radioDelivery.checked;
    document.getElementById('optDelivery').classList.toggle('delivery-option--active', isDelivery);
    document.getElementById('optPickup').classList.toggle('delivery-option--active', !isDelivery);
    instaPayCard.style.display = isDelivery ? 'block' : 'none';
    shippingRow.style.display = isDelivery ? '' : 'none';
    renderSummary();
  }

  radioDelivery?.addEventListener('change', updateDeliveryMethod);
  radioPickup?.addEventListener('change', updateDeliveryMethod);
  document.querySelectorAll('.delivery-option').forEach(el => {
    el.addEventListener('click', () => {
      el.querySelector('input').checked = true;
      updateDeliveryMethod();
    });
  });

  // Render order summary
  function renderSummary() {
    const subtotal = CART.subtotal();
    const isDelivery = radioDelivery?.checked !== false;
    const shipping = isDelivery ? shippingFee : 0;
    const total = subtotal + shipping;

    document.getElementById('orderSummaryItems').innerHTML = items.map(i => `
      <div class="order-item">
        <img src="${i.image}" alt="${i.name}" class="order-item__img"
             onerror="this.src='https://placehold.co/60x50/f5f5f5/999?text=+'">
        <div>
          <div class="order-item__name">${i.name}</div>
          <div class="order-item__qty">الكمية: ${i.qty}</div>
        </div>
        <div class="order-item__price">${formatPrice(i.price * i.qty)}</div>
      </div>`).join('');

    document.getElementById('sumSubtotal').textContent = formatPrice(subtotal);
    document.getElementById('sumShipping').textContent = isDelivery ? formatPrice(shippingFee) : 'مجاني';
    document.getElementById('sumTotal').textContent = formatPrice(total);
  }

  renderSummary();
  updateDeliveryMethod();

  // Validation
  function validate() {
    let valid = true;
    const fields = [
      { id: 'fullName', errId: 'errFullName', check: v => v.trim().length >= 3 },
      { id: 'phone', errId: 'errPhone', check: v => /^[\d+\s\-]{8,15}$/.test(v.trim()) },
      { id: 'whatsapp', errId: 'errWhatsapp', check: v => /^[\d+\s\-]{8,15}$/.test(v.trim()) },
      { id: 'city', errId: 'errCity', check: v => v.trim().length >= 2 },
      { id: 'area', errId: 'errArea', check: v => v.trim().length >= 2 },
      { id: 'address', errId: 'errAddress', check: v => v.trim().length >= 10 },
    ];

    fields.forEach(({ id, errId, check }) => {
      const input = document.getElementById(id);
      const err = document.getElementById(errId);
      const ok = check(input.value);
      input.classList.toggle('error', !ok);
      err.classList.toggle('form-error--show', !ok);
      if (!ok) valid = false;
    });

    // InstaPay confirm checkbox + payment proof (only for delivery)
    const isDelivery = radioDelivery?.checked !== false;
    if (isDelivery) {
      const confirmed = document.getElementById('payConfirm')?.checked;
      document.getElementById('errPayConfirm').classList.toggle('form-error--show', !confirmed);
      if (!confirmed) valid = false;

      const proofFile = document.getElementById('paymentProof')?.files?.[0];
      document.getElementById('errPaymentProof').classList.toggle('form-error--show', !proofFile);
      if (!proofFile) valid = false;
    }

    return valid;
  }

  // Place order
  document.getElementById('placeOrderBtn')?.addEventListener('click', async () => {
    if (!validate()) {
      showToast('يرجى ملء جميع الحقول المطلوبة', 'error');
      const firstError = document.querySelector('.form-input.error, input.error');
      if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    const name = document.getElementById('fullName').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const whatsapp = document.getElementById('whatsapp').value.trim();
    const city = document.getElementById('city').value.trim();
    const area = document.getElementById('area').value.trim();
    const address = document.getElementById('address').value.trim();
    const notes = document.getElementById('notes').value.trim();
    const transRef = document.getElementById('transRef')?.value.trim() || 'غير متوفر';
    const deliveryMethod = document.querySelector('input[name="deliveryMethod"]:checked').value;
    const isDelivery = deliveryMethod === 'delivery';

    const subtotal = CART.subtotal();
    const shipping = isDelivery ? shippingFee : 0;
    const total = subtotal + shipping;
    const orderNum = generateOrderNumber();
    const orderDate = new Date().toLocaleDateString('ar-EG', { year:'numeric', month:'long', day:'numeric' });

    // Build WhatsApp message (Arabic)
    const waMsg = [
      `🛒 *طلب جديد — ${CONFIG.storeName}*`,
      `────────────────────`,
      `📋 *رقم الطلب: ${orderNum}*`,
      `📅 التاريخ: ${orderDate}`,
      ``,
      `👤 *بيانات العميل*`,
      `الاسم: ${name}`,
      `الهاتف: ${phone}`,
      `واتساب: ${whatsapp}`,
      ``,
      `📍 *عنوان التوصيل*`,
      `المحافظة: ${city}`,
      `المنطقة: ${area}`,
      `العنوان: ${address}`,
      `طريقة الاستلام: ${isDelivery ? '🚚 توصيل للمنزل' : '🏪 استلام من المتجر'}`,
      notes ? `ملاحظات: ${notes}` : '',
      ``,
      `🛍 *المنتجات المطلوبة*`,
      ...items.map(i => `• ${i.name} × ${i.qty} = ${formatPrice(i.price * i.qty)}`),
      ``,
      `💰 *إجمالي الطلب*`,
      `المجموع الفرعي: ${formatPrice(subtotal)}`,
      `الشحن: ${isDelivery ? formatPrice(shippingFee) : 'مجاني (استلام)'}`,
      `*الإجمالي: ${formatPrice(total)}*`,
      ``,
      `💳 *الدفع*`,
      isDelivery ? `✅ تم دفع رسوم الشحن ${formatPrice(shippingFee)} عبر إنستاباي` : '',
      isDelivery ? `رقم المعاملة: ${transRef}` : '',
    ].filter(l => l !== '').join('\n');

    // Save order to sessionStorage for confirmation page
    const orderData = { orderNum, name, phone, whatsapp, city, area, address, notes, items, subtotal, shipping, total, deliveryMethod, orderDate, waMsg };
    sessionStorage.setItem('kn_last_order', JSON.stringify(orderData));

    // Save order to the store database so it shows up in the dashboard
    try {
      const formData = new FormData();
      formData.append('orderNum', orderNum);
      formData.append('name', name);
      formData.append('phone', phone);
      formData.append('whatsapp', whatsapp);
      formData.append('city', city);
      formData.append('area', area);
      formData.append('address', address);
      formData.append('notes', notes);
      formData.append('deliveryMethod', deliveryMethod);
      formData.append('transRef', transRef);
      formData.append('subtotal', subtotal);
      formData.append('shipping', shipping);
      formData.append('total', total);
      formData.append('items', JSON.stringify(items));
      const proofFile = document.getElementById('paymentProof')?.files?.[0];
      if (proofFile) formData.append('payment_proof', proofFile);

      await fetch('php/save-order.php', { method: 'POST', body: formData });
    } catch (e) {
      console.warn('[Checkout] Failed to save order to database:', e);
    }

    // Clear cart
    CART.clear();

    // Redirect to confirmation — WhatsApp is offered there as an optional step, not forced
    window.location.href = 'order-confirmation.html';
  });
});
