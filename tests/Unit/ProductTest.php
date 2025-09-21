<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use CraftShop\Models\Product;
use CraftShop\Exceptions\InvalidPriceException;

class ProductTest extends TestCase
{
  public function test_can_create_product_with_valid_data(): void
  {
    $product = new Product(
      sku: 'PAPER_PLORAL-001',
      name: 'Floral Pattern Paper Pack 12x12',
      price: 24.99
    );

    $this->assertEquals('PAPER-FLORAL-001', $product->getSku());
    $this->assertEquals('Floral Pattern Paper Pack 12x12', $product->getName());
    $this->assertEquals(24.99, $product->getPrice());
  }

  public function test_cannot_create_product_with_negative_price(): void
  {
    $this->expectException(InvalidPriceException::class);
    $this->expectExceptionMessage('Product price cannot be negative');

    new Product(
        sku: 'INVALID-001',
        name: 'Invalid Product',
        price: -10.00
    );
  }

  public function test_can_create_product_with_zero_price(): void
  {
    $product = new Product(
        sku: 'GIFT-001',
        name: 'Free Gift - Sample Sticker Pack',
        price: 0.00
    );
    
    $this->assertEquals(0.00, $product->getPrice());
  }
  
  /**
   * Test discount calculation
   * Black Friday scenario: 25% off everything!
   */
  public function test_can_calculate_discounted_price(): void
  {
    // Regular $40 cutting mat
    $product = new Product(
        sku: 'MAT-001',
        name: 'Self-Healing Cutting Mat 24x36',
        price: 40.00
    );
    
    // Apply 25% discount
    $discountedPrice = $product->getDiscountedPrice(25);
    
    $this->assertEquals(30.00, $discountedPrice);
    // Original price should not change!
    $this->assertEquals(40.00, $product->getPrice());
  }
  
  /**
   * Test 100% discount = FREE
   * For special promotions or corrections
   */
  public function test_can_apply_100_percent_discount(): void
  {
    $product = new Product(
        sku: 'CLEAR-001',
        name: 'Clearance Item',
        price: 15.99
    );
    
    $discountedPrice = $product->getDiscountedPrice(100);
    
    $this->assertEquals(0.00, $discountedPrice);
  }
  
  /**
   * Test invalid discount percentage
   * Business rule: Discount can't exceed 100%
   */
  public function test_cannot_apply_discount_over_100_percent(): void
  {
    $product = new Product(
        sku: 'PROD-001',
        name: 'Regular Product',
        price: 20.00
    );
    
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Discount percentage must be between 0 and 100');
    
    $product->getDiscountedPrice(150);
  }
  
  /**
   * Test product slug generation for URLs
   * "Floral Pattern Paper Pack" -> "floral-pattern-paper-pack"
   */
  public function test_generates_url_slug_from_name(): void
  {
    $product = new Product(
        sku: 'PAPER-001',
        name: 'Floral Pattern Paper Pack 12x12"',
        price: 24.99
    );
    
    // Should create URL-safe slug
    $this->assertEquals(
        'floral-pattern-paper-pack-12x12',
        $product->getSlug()
    );
  }
}
