  </main><!-- /.page-content -->
</div><!-- /.main-wrapper -->

<div class="toast-container" id="toastContainer"></div>

<script>
function toggleSidebar() {
    const s = document.getElementById('sidebar');
    const o = document.getElementById('overlay');
    s.classList.toggle('open');
    o.classList.toggle('show');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('show');
}
function showToast(msg, type = '') {
    const t = document.createElement('div');
    t.className = 'toast ' + type;
    t.textContent = msg;
    document.getElementById('toastContainer').appendChild(t);
    setTimeout(() => t.remove(), 3000);
}

// ── New order polling ───────────────────────────────────────
(function () {
    const LAST_ORDER_KEY = 'kn_admin_last_order_id';
    const POLL_MS = 20000;
    let lastOrderId = parseInt(localStorage.getItem(LAST_ORDER_KEY) || '0', 10);
    let firstCheck = true;

    function setBadge(id, count) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = count;
        el.style.display = count > 0 ? 'inline-flex' : 'none';
    }

    async function checkStatus() {
        try {
            const res = await fetch('ajax-status.php', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (!data.success) return;

            setBadge('ordersBadge', data.pendingOrders);
            setBadge('messagesBadge', data.unreadMessages);

            if (!firstCheck && data.latestOrderId > lastOrderId) {
                showToast('🛒 طلب جديد #' + data.latestOrderNum + ' من ' + data.latestCustomer, 'success');
                if (document.getElementById('newOrderSound')) {
                    document.getElementById('newOrderSound').play().catch(() => {});
                }
            }
            if (data.latestOrderId > lastOrderId) {
                lastOrderId = data.latestOrderId;
                localStorage.setItem(LAST_ORDER_KEY, String(lastOrderId));
            }
            firstCheck = false;
        } catch (e) { /* silently ignore network hiccups */ }
    }

    checkStatus();
    setInterval(checkStatus, POLL_MS);
})();
</script>

</body>
</html>
