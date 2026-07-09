<?php
include_once 'includes/header.php';
?>
<section class="page-header">
    <h1>RegoConnect — User Guide</h1>
    <p>This guide explains how to access the site, use the Admin area, manage products, shipping, orders, pages and payments.</p>
</section>
<section class="form-card">
    <h2>3.2 Basics: Accessing your website and Admin</h2>
    <h3>3.2.1 Accessing the website</h3>
    <p>Customer homepage: <a href="index.php">/index.php</a> or your domain root (e.g., https://regoconnect.example/).</p>
    <p>Customer login: <a href="login.php">/login.php</a></p>
    <p>Seller dashboard: <a href="dashboard.php">/dashboard.php</a> (requires seller account)</p>
    <p>Admin area: <a href="admin/login.php">/admin/login.php</a> — this is restricted to accounts with the `admin` role.</p>

    <h3>3.2.2 The Admin Area</h3>
    <p>The Admin area is used to manage users, listings, and orders across the platform. Use the admin login page to sign in and access:</p>
    <ul>
        <li>Users — review or remove accounts</li>
        <li>Listings — archive or delete problematic products</li>
        <li>Orders — update order statuses (paid, shipped, completed)</li>
    </ul>
    <p>Admin pages are under <strong>/admin/</strong>. Example visuals are available in <a href="assets/img/admin-panel.svg">admin panel image</a>.</p>

    <h2>3.3 Products: Adding, removing, and updating products</h2>
    <h3>3.3.1 Adding and Removing Products</h3>
    <p>Sellers add products from <a href="listings.php">/listings.php</a> when logged in as a seller. Fill the form title, description, price, stock, shipping settings, and save. To remove a product, sellers use the "Delete" action on their listings page. Admins can also delete listings from <a href="admin/products.php">/admin/products.php</a>.</p>

    <h3>3.3.2 Updating Products</h3>
    <p>To update a product, sellers click "Edit" on a listing and change fields. Changes are saved to the database immediately when submitted.</p>

    <h2>3.4 Changing menus</h2>
    <p>There is no UI to update the top navigation. To change menus, edit <strong>includes/header.php</strong> and adjust the links. For a dynamic menu feature, we can add a settings UI and database-backed menu items.</p>

    <h2>3.5 Shipping Options</h2>
    <p>Shipping options are per-listing. Each listing has:</p>
    <ul>
        <li><strong>shipping_type</strong>: standard, express, or pickup</li>
        <li><strong>shipping_cost</strong>: fixed cost (R)</li>
    </ul>
    <p>To use weight- or price-based shipping, we can extend listings and add a shipping rules table; I can implement that on request.</p>

    <h2>3.6 The front page: adding and changing images</h2>
    <p>The frontpage hero image is stored in <strong>assets/img/frontpage-hero.svg</strong>. Replace this file with your new SVG/PNG and it will display on the homepage.</p>

    <h2>3.7 Changing the logo</h2>
    <p>The site brand text is in <strong>includes/header.php</strong>. To use an image logo replace `.brand` content with an `<img>` tag pointing to an `assets/img/logo.png` file and update CSS accordingly.</p>

    <h2>3.8 Orders</h2>
    <p>When a buyer places an order the system creates an `orders` record with status `pending` or `paid` (if payment was simulated). Sellers and admins must:</p>
    <ol>
        <li>Verify payment (if pending)</li>
        <li>Mark item as shipped and provide tracking details (update order status to `shipped`)</li>
        <li>Once the buyer confirms delivery, update status to `completed`</li>
    </ol>

    <h2>3.9 Updating a page on your site</h2>
    <p>To update static pages (like the homepage or documentation) edit the corresponding PHP file and save. To add a new page, create a new `.php` file in the repo and link it from <strong>includes/header.php</strong>.</p>

    <h2>3.10 Payments</h2>
    <p>Payments are simulated by default. Payment methods supported in the UI: card (simulated), EFT, and Cash on Delivery.</p>
    <p>To enable a real gateway (Stripe, PayFast, etc.) I can integrate a payment provider and update the `orders` flow to verify real payment webhooks.</p>

    <h2>3.11 Checking Web Traffic and Statistics</h2>
    <p>We recommend adding Google Analytics or Matomo. To add Google Analytics, insert the GA script into <strong>includes/header.php</strong> before the closing `</head>` tag and configure your tracking ID.</p>

    <h3>Theme and seller/buyer colors</h3>
    <p>The site uses green shades for the brand. We include CSS variables in <strong>assets/css/style.css</strong>. If you'd like different shades for seller and buyer UI, we can add conditional body classes (`.role-seller`, `.role-buyer`) and style them with slightly different greens and greys.</p>
</section>
<?php include_once 'includes/footer.php'; ?>