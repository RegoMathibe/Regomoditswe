<?php
include_once 'includes/auth.php';
include_once 'includes/db.php';
include_once 'includes/functions.php';

if (!isSeller()) {
    redirect('dashboard.php');
}

$message = '';
$listing = null;
$editMode = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 1);
    $shipping_type = in_array($_POST['shipping_type'] ?? 'standard', ['standard','express','pickup']) ? $_POST['shipping_type'] : 'standard';
    $shipping_cost = floatval($_POST['shipping_cost'] ?? 0);
    $status = in_array($_POST['status'] ?? 'active', ['active','draft','archived']) ? $_POST['status'] : 'active';

    if ($_POST['action'] === 'save' && $title && $description && $price >= 0) {
        if (!empty($_POST['listing_id'])) {
            $stmt = $conn->prepare('UPDATE listings SET title=?, description=?, price=?, stock=?, shipping_type=?, shipping_cost=?, status=? WHERE listing_id=? AND user_id=?');
            $stmt->bind_param('sdiisdssi', $title, $description, $price, $stock, $shipping_type, $shipping_cost, $status, $_POST['listing_id'], $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
            $message = 'Listing updated successfully.';
        } else {
            $stmt = $conn->prepare('INSERT INTO listings (user_id, title, description, price, stock, shipping_type, shipping_cost, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('isdidiss', $_SESSION['user_id'], $title, $description, $price, $stock, $shipping_type, $shipping_cost, $status);
            $stmt->execute();
            $stmt->close();
            $message = 'Listing created successfully.';
        }
    }
}

if (isset($_GET['action'], $_GET['listing_id']) && $_GET['action'] === 'delete') {
    $stmt = $conn->prepare('DELETE FROM listings WHERE listing_id = ? AND user_id = ?');
    $stmt->bind_param('ii', $_GET['listing_id'], $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    $message = 'Listing removed.';
}

if (isset($_GET['action'], $_GET['listing_id']) && $_GET['action'] === 'edit') {
    $editMode = true;
    $stmt = $conn->prepare('SELECT listing_id, title, description, price, stock, shipping_type, shipping_cost, status FROM listings WHERE listing_id = ? AND user_id = ? LIMIT 1');
    $stmt->bind_param('ii', $_GET['listing_id'], $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($listing_id, $title, $description, $price, $stock, $shipping_type, $shipping_cost, $status);
    if ($stmt->fetch()) {
        $listing = compact('listing_id', 'title', 'description', 'price', 'stock', 'shipping_type', 'shipping_cost', 'status');
    }
    $stmt->close();
}

$listings = [];
$stmt = $conn->prepare('SELECT listing_id, title, description, price, stock, shipping_type, shipping_cost, status, created_at FROM listings WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($lid, $title, $description, $price, $stock, $shipping_type, $shipping_cost, $status, $created_at);
while ($stmt->fetch()) {
    $listings[] = compact('lid', 'title', 'description', 'price', 'stock', 'shipping_type', 'shipping_cost', 'status', 'created_at');
}
$stmt->close();

include_once 'includes/header.php';
?>
<section class="page-header">
    <h1>My Listings</h1>
    <p>Create, update, and manage the products you sell on RegoConnect.</p>
</section>
<?php if ($message): ?>
    <?php echo flashMessage($message, 'success'); ?>
<?php endif; ?>
<div class="form-card">
    <h2><?php echo $editMode ? 'Edit listing' : 'Add new listing'; ?></h2>
    <form method="POST">
        <input type="hidden" name="listing_id" value="<?php echo $listing['listing_id'] ?? ''; ?>">
        <input type="hidden" name="action" value="save">
        <label>Title</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($listing['title'] ?? ''); ?>" required>
        <label>Description</label>
        <textarea name="description" required><?php echo htmlspecialchars($listing['description'] ?? ''); ?></textarea>
        <label>Price (R)</label>
        <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($listing['price'] ?? '0.00'); ?>" required>
        <label>Stock</label>
        <input type="number" name="stock" value="<?php echo htmlspecialchars($listing['stock'] ?? '1'); ?>" required>
        <label>Shipping type</label>
        <select name="shipping_type">
            <option value="standard"<?php echo (isset($listing['shipping_type']) && $listing['shipping_type']==='standard') ? ' selected' : ''; ?>>Standard</option>
            <option value="express"<?php echo (isset($listing['shipping_type']) && $listing['shipping_type']==='express') ? ' selected' : ''; ?>>Express</option>
            <option value="pickup"<?php echo (isset($listing['shipping_type']) && $listing['shipping_type']==='pickup') ? ' selected' : ''; ?>>Local pickup</option>
        </select>
        <label>Shipping cost (R)</label>
        <input type="number" step="0.01" name="shipping_cost" value="<?php echo htmlspecialchars($listing['shipping_cost'] ?? '0.00'); ?>" required>
        <label>Status</label>
        <select name="status">
            <option value="active"<?php echo (isset($listing['status']) && $listing['status']==='active') ? ' selected' : ''; ?>>Active</option>
            <option value="draft"<?php echo (isset($listing['status']) && $listing['status']==='draft') ? ' selected' : ''; ?>>Draft</option>
            <option value="archived"<?php echo (isset($listing['status']) && $listing['status']==='archived') ? ' selected' : ''; ?>>Archived</option>
        </select>
        <button class="btn btn-primary" type="submit"><?php echo $editMode ? 'Update listing' : 'Create listing'; ?></button>
    </form>
</div>
<?php if (!empty($listings)): ?>
    <div class="product-grid">
        <?php foreach ($listings as $item): ?>
            <article class="product-card seller-card">
                <h2><?php echo htmlspecialchars($item['title']); ?></h2>
                <p><?php echo htmlspecialchars($item['description']); ?></p>
                <p><strong>R <?php echo number_format($item['price'],2); ?></strong></p>
                <p>Status: <?php echo htmlspecialchars($item['status']); ?></p>
                <div class="card-actions">
                    <a class="btn btn-secondary" href="listings.php?action=edit&listing_id=<?php echo $item['lid']; ?>">Edit</a>
                    <a class="btn btn-danger" href="listings.php?action=delete&listing_id=<?php echo $item['lid']; ?>" onclick="return confirm('Remove this listing?');">Delete</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php include_once 'includes/footer.php'; ?>