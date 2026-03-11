/* TiCi NatureLab – Shop JS */
var SITE_URL = document.location.origin + '/tici-shop';

$(function () {

  // ── Add to Cart (AJAX) ─────────────────────────────────────
  $(document).on('click', '.btn-add-to-cart', function (e) {
    e.preventDefault();
    var $btn = $(this);
    var productId = $btn.data('id');
    var variantId = $('.variant-btn.active').data('id') || 0;
    var qty = parseInt($('.qty-value').val() || 1);
    var origHtml = $btn.html();
    $btn.html('<i class="fa fa-spinner fa-spin"></i> Adding…').prop('disabled', true);
    $.post(SITE_URL + '/cart/add.php', { product_id: productId, variant_id: variantId, quantity: qty }, function (res) {
      if (res.success) {
        $('.cart-count').text(res.cart_count);
        showToast('✅ Added to cart!', 'success');
        $btn.html('<i class="fa fa-check"></i> Added!');
        setTimeout(function () { $btn.html(origHtml).prop('disabled', false); }, 1800);
      } else {
        showToast(res.message || 'Could not add', 'error');
        $btn.html(origHtml).prop('disabled', false);
      }
    }, 'json').fail(function () { showToast('Error. Try again.', 'error'); $btn.html(origHtml).prop('disabled', false); });
  });

  // ── Wishlist ───────────────────────────────────────────────
  $(document).on('click', '.btn-wishlist', function (e) {
    e.preventDefault();
    var $btn = $(this);
    $.post(SITE_URL + '/account/wishlist-toggle.php', { product_id: $btn.data('id') }, function (res) {
      if (res.success) {
        $btn.toggleClass('wished', res.added);
        $btn.find('i').toggleClass('fa-regular', !res.added).toggleClass('fa-solid', res.added);
        showToast(res.added ? '❤️ Added to wishlist' : 'Removed from wishlist', 'success');
      } else if (res.redirect) { window.location = res.redirect; }
      else showToast(res.message, 'error');
    }, 'json');
  });

  // ── Qty Controls ──────────────────────────────────────────
  $(document).on('click', '.qty-dec', function () {
    var $i = $(this).siblings('.qty-value'); $i.val(Math.max(1, parseInt($i.val()) - 1)).trigger('change');
  });
  $(document).on('click', '.qty-inc', function () {
    var $i = $(this).siblings('.qty-value'); $i.val(parseInt($i.val()) + 1).trigger('change');
  });

  // Cart live update
  $(document).on('change', '.cart-qty-input', function () {
    var itemId = $(this).data('item-id'), qty = parseInt($(this).val());
    $.post(SITE_URL + '/cart/update.php', { item_id: itemId, quantity: qty }, function (res) {
      if (res.success) { updateTotals(res); $('.cart-count').text(res.cart_count); }
    }, 'json');
  });

  $(document).on('click', '.btn-remove-cart', function () {
    var $row = $(this).closest('tr');
    $.post(SITE_URL + '/cart/update.php', { item_id: $(this).data('item-id'), quantity: 0 }, function (res) {
      if (res.success) { $row.fadeOut(300, function () { $(this).remove(); updateTotals(res); $('.cart-count').text(res.cart_count); if (res.cart_count == 0) location.reload(); }); }
    }, 'json');
  });

  function updateTotals(res) {
    if (res.subtotal !== undefined) {
      $('#cart-subtotal').text('₹' + fmt(res.subtotal));
      $('#cart-shipping').text(res.shipping > 0 ? '₹' + fmt(res.shipping) : 'FREE');
      $('#cart-discount').text(res.discount > 0 ? '−₹' + fmt(res.discount) : '—');
      $('#cart-total').text('₹' + fmt(res.total));
    }
  }

  // ── Coupon ────────────────────────────────────────────────
  $(document).on('click', '#applyCouponBtn', function () {
    var code = $('#couponCode').val().trim().toUpperCase();
    if (!code) return;
    $(this).html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
    $.post(SITE_URL + '/cart/coupon.php', { code: code }, function (res) {
      if (res.success) {
        $('#couponMsg').html('<span style="color:var(--green-mid)"><i class="fa fa-check-circle"></i> ' + res.message + '</span>');
        updateTotals(res); showToast('🎉 ' + res.message, 'success');
      } else {
        $('#couponMsg').html('<span style="color:var(--terracotta)"><i class="fa fa-times-circle"></i> ' + res.message + '</span>');
        showToast(res.message, 'error');
      }
      $('#applyCouponBtn').html('Apply').prop('disabled', false);
    }, 'json');
  });

  // ── Gallery ───────────────────────────────────────────────
  $(document).on('click', '.gallery-thumb', function () {
    var src = $(this).data('src');
    if (src) { $('#mainGalleryImg').attr('src', src).show(); $('#mainGalleryEmoji').hide(); }
    else { $('#mainGalleryImg').hide(); $('#mainGalleryEmoji').text($(this).data('emoji')).show(); }
    $('.gallery-thumb').removeClass('active'); $(this).addClass('active');
  });

  // ── Variants ──────────────────────────────────────────────
  $(document).on('click', '.variant-btn', function () {
    $('.variant-btn').removeClass('active'); $(this).addClass('active');
    if ($(this).data('price')) $('.detail-price').text('₹' + fmt($(this).data('price')));
  });

  // ── Filter toggle mobile ──────────────────────────────────
  $('#filterToggle').on('click', function () { $('#shopSidebar').toggleClass('mobile-open'); });
  $('#filterClose').on('click', function () { $('#shopSidebar').removeClass('mobile-open'); });

  // ── Toast ─────────────────────────────────────────────────
  window.showToast = function (msg, type) {
    if (!$('#tc').length) $('body').append('<div id="tc" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;max-width:320px;pointer-events:none"></div>');
    var colors = { success: ['#d4edda','#1e5c38','#b8ddc9'], error: ['#fde8e8','#9b2c2c','#f5c2c2'], info: ['#e8f4fd','#1a5c8a','#b8d9f0'] };
    var c = colors[type] || colors.success;
    var $t = $('<div style="background:' + c[0] + ';color:' + c[1] + ';border:1px solid ' + c[2] + ';padding:12px 16px;border-radius:10px;font-size:.875rem;font-weight:500;box-shadow:0 4px 16px rgba(0,0,0,.12);pointer-events:all">' + msg + '</div>');
    $('#tc').append($t);
    setTimeout(function () { $t.fadeOut(300, function () { $t.remove(); }); }, 3500);
  };

  // ── Scroll reveal ─────────────────────────────────────────
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) {
          e.target.style.opacity = '1';
          e.target.style.transform = 'translateY(0)';
          io.unobserve(e.target);
        }
      });
    }, { threshold: .08 });
    document.querySelectorAll('.product-card, .review-card, .blog-card, .cat-pill').forEach(function (el) {
      el.style.cssText += 'opacity:0;transform:translateY(18px);transition:opacity .45s ease,transform .45s ease';
      io.observe(el);
    });
  }

  function fmt(n) { return parseFloat(n).toLocaleString('en-IN', { minimumFractionDigits: 2 }); }
});
