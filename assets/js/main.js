/* assets/js/main.js
   Defensive, dependency-light JS:
   - Responsive nav toggle (if nav-toggle exists)
   - Quick View modal: pulls server-rendered hidden review block (id "reviews-{code}")
   - Modal accessibility: focus trap basics, Escape to close, backdrop click
   - Finds CSRF token from page forms when adding quick add-to-cart inside modal
*/

document.addEventListener('DOMContentLoaded', function () {
  // NAV TOGGLE (if present)
  (function navToggle() {
    const toggle = document.querySelector('.nav-toggle');
    const navList = document.querySelector('.main-nav .nav-list');
    if (!toggle || !navList) return;
    toggle.addEventListener('click', function () {
      navList.classList.toggle('open');
      toggle.classList.toggle('open');
    });
  })();

  // Modal elements
  const modal = document.getElementById('modal');
  const modalContent = modal ? modal.querySelector('#modal-content') : null;

  function isNode(o) { return o instanceof Element || o instanceof HTMLDocument; }

  // Utility: sanitize code for safe id building (same as server-side)
  function safeIdFromCode(code) {
    return 'reviews-' + String(code).replace(/[^A-Za-z0-9_-]/g, '');
  }

  // Utility: get CSRF token from any existing form on the page
  function getCsrfToken() {
    const el = document.querySelector('input[name="csrf_token"]');
    return el ? el.value : '';
  }

  // Modal open/close helpers
  let lastFocused = null;
  function openModal(html) {
    if (!modal || !modalContent) return;
    lastFocused = document.activeElement;
    modalContent.innerHTML = html;
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    // set focus to first focusable element in modal
    const focusable = modal.querySelector('button, a, input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (focusable) focusable.focus();
  }
  function closeModal() {
    if (!modal || !modalContent) return;
    modalContent.innerHTML = '';
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
  }

  // Close handlers: backdrop and elements with data-modal-close
  if (modal) {
    modal.addEventListener('click', function (e) {
      // close when clicking backdrop (not panel)
      if (e.target.classList.contains('modal-backdrop')) closeModal();
    });
    modal.querySelectorAll('[data-modal-close]').forEach(btn => {
      btn.addEventListener('click', closeModal);
    });
  }

  // Escape key to close modal
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal && modal.getAttribute('aria-hidden') === 'false') {
      closeModal();
    }
  });

  // Helper: build quick view HTML by combining product card context and hidden reviews
  function buildQuickViewContent(productCard, hiddenReviewsHtml, code) {
    const imgHtml = productCard.querySelector('.product-media') ? productCard.querySelector('.product-media').innerHTML : '';
    const title = productCard.querySelector('.product-name') ? productCard.querySelector('.product-name').innerText : '';
    const priceHtml = productCard.querySelector('.product-price') ? productCard.querySelector('.product-price').innerHTML : '';
    const csrf = getCsrfToken();

    // Quick add-to-cart form uses CSRF if available
    const addToCartForm = `
      <form method="POST" style="display:flex;gap:.5rem;align-items:center;margin-top:.6rem">
        ${csrf ? `<input type="hidden" name="csrf_token" value="${csrf}">` : ''}
        <input type="hidden" name="add_to_cart" value="1">
        <input type="hidden" name="product_code" value="${encodeURIComponent(code)}">
        <input type="number" name="quantity" value="1" min="1" style="width:76px;padding:.4rem;border-radius:8px;border:1px solid rgba(11,20,28,0.06);background:transparent;">
        <button class="btn btn-primary btn-sm" type="submit">Add to cart</button>
        <a class="btn btn-outline btn-sm" href="cart.php?buy&code=${encodeURIComponent(code)}">Buy now</a>
      </form>
    `;

    return `
      <div style="display:grid;grid-template-columns:260px 1fr;gap:1rem;">
        <div style="display:flex;align-items:center;justify-content:center">${imgHtml}</div>
        <div>
          <h2 style="margin-top:0">${escapeHtml(title)}</h2>
          <div>${priceHtml}</div>
          ${addToCartForm}
          <hr style="margin:.7rem 0;border:none;border-top:1px dashed rgba(11,20,28,0.06)">
          <div>${hiddenReviewsHtml}</div>
        </div>
      </div>
    `;
  }

  // Simple HTML escape for inserted text
  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>"']/g, function (m) {
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m];
    });
  }

  // Event delegation for quick view / open reviews
  document.addEventListener('click', function (e) {
    const target = e.target.closest && e.target.closest('.btn-quickview, .btn-open-reviews');
    if (!target) return;
    e.preventDefault();

    const code = target.dataset.product;
    if (!code) return;

    const safeId = safeIdFromCode(code);
    const hidden = document.getElementById(safeId);
    const productCard = target.closest('.product-card');

    let hiddenHtml = '';
    if (hidden) {
      // Use the innerHTML of the hidden server-rendered block
      hiddenHtml = hidden.innerHTML;
    } else {
      hiddenHtml = `<p style="color:#6b7280">No review data available for this product.</p>`;
    }

    // Build quick-view UI using product context and hidden reviews
    const content = productCard ? buildQuickViewContent(productCard, hiddenHtml, code) : hiddenHtml;
    openModal(content);
  });

  // Safety: avoid errors if elements are missing
  // (nothing more to initialize)
});
