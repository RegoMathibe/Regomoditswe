<?php
// Simple seed script to add example listings for the sample seller
include_once 'includes/db.php';

$sellerEmail = 'seller@regoconnect.local';
$check = $conn->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
$check->bind_param('s', $sellerEmail);
$check->execute();
$check->bind_result($sellerId);
if (!$check->fetch()) {
    echo "Sample seller not found. Create a seller account first (email: seller@regoconnect.local).\n";
    exit();
}
$check->close();

$existing = $conn->prepare('SELECT COUNT(*) FROM listings WHERE user_id = ?');
$existing->bind_param('i', $sellerId); $existing->execute(); $existing->bind_result($count); $existing->fetch(); $existing->close();
if ($count > 0) {
    echo "Seller already has listings. Skipping seeding.\n";
    exit();
}

$items = [
    ['Bamboo cutting board', 'Eco-friendly bamboo board, 30x20cm.', 129.99, 8, 'standard', 25.00],
    ['Handcrafted mug', 'Ceramic mug, dishwasher-safe.', 89.50, 15, 'standard', 18.00],
    ['Local delivery bundle', 'Small box with regional treats.', 249.00, 5, 'express', 40.00]
];

$ins = $conn->prepare('INSERT INTO listings (user_id, title, description, price, stock, shipping_type, shipping_cost, status) VALUES (?, ?, ?, ?, ?, ?, ?, "active")');
foreach ($items as $it) {
    $ins->bind_param('issdids', $sellerId, $it[0], $it[1], $it[2], $it[3], $it[4], $it[5]);
    $ins->execute();
}
$ins->close();

echo "Seeded sample listings for seller ID: $sellerId\n";
?>