<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart - CraftShop</title>
</head>
<body>
    <h1>Your Shopping Cart</h1>
    
    <?php if (empty($items)): ?>
        <p>Your cart is empty</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['product']->getName()) ?></td>
                <td>$<?= number_format($item['product']->getPrice(), 2) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>$<?= number_format($item['product']->getPrice() * $item['quantity'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <h2>Total: $<?= number_format($total, 2) ?></h2>
    <?php endif; ?>
    
    <!-- Test form to add items -->
    <h3>Add Test Item</h3>
    <form method="POST" action="/cart/add">
        <input type="hidden" name="product_id" value="123">
        <input type="number" name="quantity" value="1" min="1">
        <button type="submit">Add to Cart</button>
    </form>
</body>
</html>