<?php

namespace CraftShop\Services;

use CraftShop\Models\Product;

class Cart
{
    private array $items = [];
    private string $id;
    
    public function __construct(?string $id = null)
    {
        $this->id = $id ?? uniqid('cart_');
        $this->loadFromSession();
    }
    
    public function add(Product $product, int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }
        
        $sku = $product->getSku();
        
        // If product already in cart, increase quantity
        if (isset($this->items[$sku])) {
            $this->items[$sku]['quantity'] += $quantity;
        } else {
            $this->items[$sku] = [
                'product' => $product,
                'quantity' => $quantity,
                'price' => $product->getPrice()
            ];
        }
        
        $this->saveToSession();
    }
    
    public function updateQuantity(string $sku, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($sku);
            return;
        }
        
        if (isset($this->items[$sku])) {
            $this->items[$sku]['quantity'] = $quantity;
            $this->saveToSession();
        }
    }
    
    public function remove(string $sku): void
    {
        unset($this->items[$sku]);
        $this->saveToSession();
    }
    
    public function getItems(): array
    {
        return $this->items;
    }
    
    public function getItemCount(): int
    {
        $count = 0;
        foreach ($this->items as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }
    
    public function getSubtotal(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return round($total, 2);
    }
    
    public function getDiscount(): float
    {
        // Business rule: 10% off orders over $100
        $subtotal = $this->getSubtotal();
        if ($subtotal > 100) {
            return round($subtotal * 0.10, 2);
        }
        return 0;
    }
    
    public function getTotal(): float
    {
        return $this->getSubtotal() - $this->getDiscount();
    }
    
    public function isEmpty(): bool
    {
        return empty($this->items);
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function clear(): void
    {
        $this->items = [];
        $this->saveToSession();
    }
    
    /**
     * Session persistence (simplified for testing)
     */
    private function saveToSession(): void
    {
        // In production, would use $_SESSION
        // For testing, we'll simulate with static storage
        self::$storage[$this->id] = $this->items;
    }
    
    private function loadFromSession(): void
    {
        $this->items = self::$storage[$this->id] ?? [];
    }
    
    public static function restore(string $id): self
    {
        return new self($id);
    }
    
    // Simulated session storage for testing
    private static array $storage = [];
}