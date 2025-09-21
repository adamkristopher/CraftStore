<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use CraftShop\Models\Product;
use CraftShop\Services\Cart;
use CraftShop\Exceptions\OutOfStockException;

class CartTest extends TestCase
{
    private Cart $cart;
    
    protected function setUp(): void
    {
        // Start with empty cart for each test
        $this->cart = new Cart();
    }
    
    /**
     * Basic cart operations
     */
    public function test_can_add_product_to_cart(): void
    {
        $product = new Product(
            sku: 'PAPER-001',
            name: 'Scrapbook Paper Pack',
            price: 19.99
        );
        
        $this->cart->add($product, 2);
        
        $this->assertEquals(2, $this->cart->getItemCount());
        $this->assertEquals(39.98, $this->cart->getSubtotal());
    }
    
    public function test_can_update_quantity(): void
    {
        $product = new Product(
            sku: 'SCISSORS-001',
            name: 'Craft Scissors',
            price: 24.99
        );
        
        $this->cart->add($product, 1);
        $this->cart->updateQuantity('SCISSORS-001', 3);
        
        $this->assertEquals(3, $this->cart->getItemCount());
        $this->assertEquals(74.97, $this->cart->getSubtotal());
    }
    
    public function test_can_remove_item_from_cart(): void
    {
        $product = new Product(
            sku: 'GLUE-001',
            name: 'Craft Glue',
            price: 5.99
        );
        
        $this->cart->add($product, 2);
        $this->cart->remove('GLUE-001');
        
        $this->assertEquals(0, $this->cart->getItemCount());
        $this->assertTrue($this->cart->isEmpty());
    }
    
    public function test_adding_same_product_increases_quantity(): void
    {
        $product = new Product(
            sku: 'STAMP-001',
            name: 'Holiday Stamp Set',
            price: 15.99
        );
        
        $this->cart->add($product, 2);
        $this->cart->add($product, 3);
        
        // Should have 5 total, not separate line items
        $this->assertEquals(5, $this->cart->getItemCount());
        $this->assertEquals(1, count($this->cart->getItems()));
    }
    
    public function test_cannot_add_negative_quantity(): void
    {
        $product = new Product(
            sku: 'PAPER-002',
            name: 'Cardstock',
            price: 12.99
        );
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be positive');
        
        $this->cart->add($product, -1);
    }
    
    /**
     * Scrapbook.com business rules
     */
    public function test_applies_bulk_discount_over_100_dollars(): void
    {
        // Business rule: 10% off orders over $100
        $expensiveProduct = new Product(
            sku: 'MACHINE-001',
            name: 'Die Cutting Machine',
            price: 120.00
        );
        
        $this->cart->add($expensiveProduct, 1);
        
        $this->assertEquals(120.00, $this->cart->getSubtotal());
        $this->assertEquals(12.00, $this->cart->getDiscount());
        $this->assertEquals(108.00, $this->cart->getTotal());
    }
    
    public function test_cart_persists_in_session(): void
    {
        // Simulate session storage
        $product = new Product(
            sku: 'PAPER-003',
            name: 'Vintage Paper',
            price: 8.99
        );
        
        $this->cart->add($product, 1);
        $cartId = $this->cart->getId();
        
        // Simulate new request with same session
        $restoredCart = Cart::restore($cartId);
        
        $this->assertEquals(1, $restoredCart->getItemCount());
        $this->assertEquals(8.99, $restoredCart->getSubtotal());
    }
}