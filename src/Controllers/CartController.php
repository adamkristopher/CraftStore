<?php

namespace CraftShop\Controllers;

use CraftShop\Services\Cart;
use CraftShop\Models\Product;

class CartController
{
  private Cart $cart;

  public function __construct()
  {
    $this->cart = new Cart();
  }

  public function index(): void
  {
    // Get cart items
    $items = $this->cart->getItems();
    $total = $this->cart->getTotal();
    
    // Load view
    include __DIR__ . '/../../views/cart/index.php';
  }

  public function add(): void
  {
      // Get product from form submission
      $productId = $_POST['product_id'] ?? null;
      $quantity = (int)($_POST['quantity'] ?? 1);
      
      if (!$productId) {
          header('Location: /cart');
          exit;
      }
      
      // In real app, load from database
      // For now, create dummy product
      $product = new Product(
          sku: "PROD-{$productId}",
          name: "Product {$productId}",
          price: 29.99
      );
      
      $this->cart->add($product, $quantity);
      
      // Redirect back to cart
      header('Location: /cart');
      exit;
  }
}