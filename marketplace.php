<?php
include_once 'includes/header.php';

$listings = [];
$stmt = $conn->prepare('SELECT l.listing_id, l.title, l.description, l.price, l.stock, l.shipping_type, l.shipping_cost, u.full_name AS seller_name FROM listings l JOIN users u ON l.user_id = u.user_id WHERE l.status = "active" ORDER BY l.created_at DESC');
$stmt->execute();
$stmt->bind_result($id, $title, $description, $price, $stock, $shipping_type, $shipping_cost, $seller_name);
while ($stmt->fetch()) {
    $listings[] = compact('id', 'title', 'description', 'price', 'stock', 'shipping_type', 'shipping_cost', 'seller_name');
}
$stmt->close();
?>
<section class="page-header">
    <h1>Marketplace</h1>
    <p>Browse the latest community listings from buyers and sellers.</p>
</section>
<?php if (empty($listings)): ?>
    <div class="empty-state">No active listings yet. Sellers can add products from their dashboard.</div>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($listings as $item): ?>
            <article class="product-card">
                <h2><?php echo htmlspecialchars($item['title']); ?></h2>
                <p><?php echo htmlspecialchars($item['description']); ?></p>
                <p><strong>R <?php echo number_format($item['price'], 2); ?></strong></p>
                <p>Seller: <?php echo htmlspecialchars($item['seller_name']); ?></p>
                <p>Shipping: <?php echo htmlspecialchars(ucfirst($item['shipping_type'])); ?> (R <?php echo number_format($item['shipping_cost'], 2); ?>)</p>
                <?php if ($item['stock'] > 0): ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a class="btn btn-primary" href="checkout.php?listing_id=<?php echo $item['id']; ?>">Buy now</a>
                    <?php else: ?>
                        <a class="btn btn-secondary" href="login.php">Login to buy</a>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="tag">Out of stock</span>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php include_once 'includes/footer.php'; ?>