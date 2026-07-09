<?php
include_once 'includes/auth.php';
include_once 'includes/db.php';
include_once 'includes/functions.php';

$listing = null;
$notice = '';
if (!isset($_GET['listing_id'])) {
    redirect('marketplace.php');
}
$listing_id = intval($_GET['listing_id']);
$stmt = $conn->prepare('SELECT l.listing_id, l.title, l.description, l.price, l.stock, l.shipping_type, l.shipping_cost, u.full_name AS seller_name FROM listings l JOIN users u ON l.user_id = u.user_id WHERE l.listing_id = ? AND l.status = "active" LIMIT 1');
$stmt->bind_param('i', $listing_id);
$stmt->execute();
$stmt->bind_result($id, $title, $description, $price, $stock, $shipping_type, $shipping_cost, $seller_name);
if ($stmt->fetch()) {
    $listing = compact('id', 'title', 'description', 'price', 'stock', 'shipping_type', 'shipping_cost', 'seller_name');
}
$stmt->close();
if (!$listing) {
    $notice = 'Listing not found or unavailable.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $listing) {
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    $payment_method = in_array($_POST['payment_method'] ?? 'card', ['card','eft','cod']) ? $_POST['payment_method'] : 'card';
    $shipping_method = in_array($_POST['shipping_method'] ?? $listing['shipping_type'], ['standard','express','pickup']) ? $_POST['shipping_method'] : $listing['shipping_type'];
    $total = ($listing['price'] * $quantity) + $listing['shipping_cost'];

    $insert = $conn->prepare('INSERT INTO orders (listing_id, buyer_id, seller_id, quantity, total, payment_method, shipping_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $status = $payment_method === 'card' ? 'paid' : 'pending';
    $insert->bind_param('iiiidsss', $listing['id'], $_SESSION['user_id'], $listing['seller_name'] ? $listing['seller_name'] : $_SESSION['user_id'], $quantity, $total, $payment_method, $shipping_method, $status);
    // bug: seller_id should be numeric from listing query; fix below
    $insert->close();
}

// Fix product query to fetch seller_id and adjust order insert
$stmt = $conn->prepare('SELECT l.listing_id, l.title, l.description, l.price, l.stock, l.shipping_type, l.shipping_cost, u.user_id AS seller_id, u.full_name AS seller_name FROM listings l JOIN users u ON l.user_id = u.user_id WHERE l.listing_id = ? AND l.status = "active" LIMIT 1');
$stmt->bind_param('i', $listing_id);
$stmt->execute();
$stmt->bind_result($id, $title, $description, $price, $stock, $shipping_type, $shipping_cost, $seller_id, $seller_name);
if ($stmt->fetch()) {
    $listing = compact('id', 'title', 'description', 'price', 'stock', 'shipping_type', 'shipping_cost', 'seller_name', 'seller_id');
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $listing) {
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    $payment_method = in_array($_POST['payment_method'] ?? 'card', ['card','eft','cod']) ? $_POST['payment_method'] : 'card';
    $shipping_method = in_array($_POST['shipping_method'] ?? $listing['shipping_type'], ['standard','express','pickup']) ? $_POST['shipping_method'] : $listing['shipping_type'];
    $total = ($listing['price'] * $quantity) + $listing['shipping_cost'];

    $insert = $conn->prepare('INSERT INTO orders (listing_id, buyer_id, seller_id, quantity, total, payment_method, shipping_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $status = $payment_method === 'card' ? 'paid' : 'pending';
    $insert->bind_param('iiiidsss', $listing['id'], $_SESSION['user_id'], $listing['seller_id'], $quantity, $total, $payment_method, $shipping_method, $status);
    if ($insert->execute()) {
        $notice = 'Your order has been placed with status: ' . $status . '.';
    } else {
        $notice = 'Could not place order. Please try again.';
    }
    $insert->close();
}

include_once 'includes/header.php';
?>
<section class="page-header">
    <h1>Checkout</h1>
</section>
<?php if ($notice): ?>
    <?php echo flashMessage($notice, $listing ? 'success' : 'danger'); ?>
<?php endif; ?>
<?php if ($listing): ?>
    <div class="checkout-grid">
        <div class="checkout-card">
            <h2><?php echo htmlspecialchars($listing['title']); ?></h2>
            <p><?php echo htmlspecialchars($listing['description']); ?></p>
            <p><strong>Price:</strong> R <?php echo number_format($listing['price'], 2); ?></p>
            <p><strong>Shipping:</strong> <?php echo htmlspecialchars(ucfirst($listing['shipping_type'])); ?> (R <?php echo number_format($listing['shipping_cost'], 2); ?>)</p>
            <p><strong>Seller:</strong> <?php echo htmlspecialchars($listing['seller_name']); ?></p>
        </div>
        <div class="checkout-card">
            <form method="POST">
                <label>Quantity</label>
                <input type="number" name="quantity" value="1" min="1" max="<?php echo max(1, $listing['stock']); ?>">
                <label>Payment method</label>
                <select name="payment_method">
                    <option value="card">Card (simulated)</option>
                    <option value="eft">Bank EFT</option>
                    <option value="cod">Cash on Delivery</option>
                </select>
                <label>Shipping option</label>
                <select name="shipping_method">
                    <option value="standard"<?php echo $listing['shipping_type'] === 'standard' ? ' selected' : ''; ?>>Standard</option>
                    <option value="express"<?php echo $listing['shipping_type'] === 'express' ? ' selected' : ''; ?>>Express</option>
                    <option value="pickup"<?php echo $listing['shipping_type'] === 'pickup' ? ' selected' : ''; ?>>Local pickup</option>
                </select>
                <button class="btn btn-primary" type="submit">Place Order</button>
            </form>
        </div>
    </div>
<?php endif; ?>
<?php include_once 'includes/footer.php'; ?>