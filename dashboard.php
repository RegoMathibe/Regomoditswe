<?php
include_once 'includes/auth.php';
include_once 'includes/db.php';
include_once 'includes/functions.php';
include_once 'includes/header.php';

$userType = $_SESSION['user_type'];
$userName = htmlspecialchars($_SESSION['full_name']);
$ordersCount = 0;
$listingsCount = 0;

if ($userType === 'seller') {
    $stmt = $conn->prepare('SELECT COUNT(*) FROM listings WHERE user_id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($listingsCount);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare('SELECT COUNT(*) FROM orders WHERE seller_id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($ordersCount);
    $stmt->fetch();
    $stmt->close();
} else {
    $stmt = $conn->prepare('SELECT COUNT(*) FROM orders WHERE buyer_id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($ordersCount);
    $stmt->fetch();
    $stmt->close();
}
?>
<section class="page-header">
    <h1>Welcome back, <?php echo $userName; ?></h1>
    <p>Your RegoConnect <?php echo $userType === 'seller' ? 'seller' : 'buyer'; ?> dashboard.</p>
</section>
<div class="dashboard-grid">
    <div class="dashboard-card">
        <h3>Orders</h3>
        <p><?php echo $ordersCount; ?> order<?php echo $ordersCount === 1 ? '' : 's'; ?> linked to your account.</p>
        <a class="btn btn-secondary" href="orders.php">View orders</a>
    </div>
    <?php if ($userType === 'seller'): ?>
        <div class="dashboard-card">
            <h3>My listings</h3>
            <p><?php echo $listingsCount; ?> product<?php echo $listingsCount === 1 ? '' : 's'; ?> live or draft.</p>
            <a class="btn btn-secondary" href="listings.php">Manage listings</a>
        </div>
    <?php else: ?>
        <div class="dashboard-card">
            <h3>Marketplace</h3>
            <p>Search products, compare shipping, and place orders.</p>
            <a class="btn btn-secondary" href="marketplace.php">Go to marketplace</a>
        </div>
    <?php endif; ?>
    <div class="dashboard-card">
        <h3>Support</h3>
        <p>If you need help, use the documentation page or contact the site administrator.</p>
        <a class="btn btn-secondary" href="documentation.php">View guide</a>
    </div>
</div>
<?php include_once 'includes/footer.php'; ?>