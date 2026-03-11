# TiCi NatureLab – Frontend (Complete)

## Setup
1. Copy `tici-shop/` to `C:\xampp\htdocs\`
2. Edit `includes/config.php` → set DB_HOST, DB_USER, DB_PASS
3. The database tables auto-create on first visit (same DB as admin)
4. Visit: http://localhost/tici-shop/

## File Map
```
tici-shop/
├── index.php               ← Homepage (hero, tabs, reviews, blog, newsletter)
├── includes/
│   ├── config.php          ← DB, session, helpers, seed data
│   ├── header.php          ← Sticky header + mobile drawer nav
│   ├── footer.php          ← Footer + jQuery + shop.js
│   ├── product-card.php    ← Reusable product card partial
│   └── account-nav.php     ← Account sidebar nav partial
├── assets/
│   ├── css/style.css       ← Full site styles (organic botanical theme)
│   └── js/shop.js          ← AJAX cart, wishlist, search, toast, scroll-reveal
├── shop/
│   ├── index.php           ← Product listing + filters + pagination
│   └── product.php         ← Product detail + reviews + plant props + related
├── cart/
│   ├── index.php           ← Cart page with AJAX qty update
│   ├── checkout.php        ← Multi-step checkout with saved addresses
│   ├── order-success.php   ← Confirmation page with status tracker
│   ├── add.php             ← AJAX: add to cart
│   ├── update.php          ← AJAX: update qty / remove item
│   └── coupon.php          ← AJAX: apply coupon code
├── account/
│   ├── login.php           ← Login with cart merge
│   ├── register.php        ← Registration
│   ├── index.php           ← Account dashboard
│   ├── orders.php          ← Order history with status filter tabs
│   ├── order-detail.php    ← Order detail + progress tracker + timeline
│   ├── wishlist.php        ← Saved products
│   ├── wishlist-toggle.php ← AJAX: toggle wishlist
│   ├── addresses.php       ← Saved addresses management
│   ├── profile.php         ← Edit name/phone
│   ├── password.php        ← Change password
│   └── logout.php          ← Session destroy
└── pages/
    ├── about.php           ← About us
    ├── contact.php         ← Contact form
    ├── faq.php             ← Accordion FAQ
    ├── blog.php            ← Blog listing
    ├── track-order.php     ← Public order tracker
    ├── shipping-policy.php
    ├── return-policy.php
    ├── privacy-policy.php
    └── terms.php
```

## Key Features
- Organic Botanical design: Playfair Display + DM Sans fonts
- Dark forest green (#1a3d2b) + warm amber (#e9a83a) palette
- Full mobile responsive with hamburger drawer nav
- AJAX cart (add, update qty, remove, coupon)
- Wishlist toggle (AJAX, login-required)
- Product filters: category, price, difficulty, light requirement
- Sort: newest, popular, price asc/desc, name
- Plant properties: difficulty, light, growth rate, CO2, tank size
- Product detail: gallery, variants, reviews with star rating
- Multi-step checkout with saved addresses
- Order tracking with visual progress steps
- Scroll-reveal animations
- Toast notifications
