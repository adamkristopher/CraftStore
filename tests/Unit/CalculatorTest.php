<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use CraftShop\Calculator;

/**
 * 
 */
class CalculatorTest extends TestCase
{
  private Calculator $calculator;

  public function setUp(): void
  {
    $this->calculator = new Calculator();
  }

  public function test_can_add_two_numbers(): void
  {
    $result = $this->calculator->add(5, 3);
    $this->assertEquals(8, $result);
  }

  public function test_can_add_negative_numbers(): void
  {
    $result = $this->calculator->add(-5, 3);
    $this->assertEquals(-2, $result);
  }

  public function test_can_add_decimal_numbers(): void
  {
    $result = $this->calculator->add(19.99, 5.50);
    $this->assertEquals(25.49, $result);
  }

  public function test_can_subtract_for_discounts(): void
  {
    $originalPrice = 25.99;
    $discount = 5.00;

    $result = $this->calculator->subtract($originalPrice, $discount);

    $this->assertEquals(20.99, $result);
  }

  public function test_can_multiply_for_line_items(): void
  {
    $pricePerItme = 4.99;
    $quantity = 3;

    $result = $this->calculator->multiply($pricePerItme, $quantity);

    $this->assertEquals(14.97, $result);
  }

  public function test_can_calculate_percentage_for_tax(): void
  {
    $subtotal = 100.00;
    $taxRate = 8.25;
    $result = $this->calculator->percentage($subtotal, $taxRate);

    $this->assertEquals(108.25, $result);
  }

  public function test_handles_floating_point_precision(): void
  {
    // Common problem: 0.1 + 0.2 = 0.30000000000000004
    $result = $this->calculator->add(0.1, 0.2);
    
    // PHPUnit's assertEquals handles float comparison with delta
    $this->assertEquals(0.3, $result);
    
    // Multiple small items
    $result = $this->calculator->add(19.99, 29.99);
    $result = $this->calculator->add($result, 9.99);
    
    $this->assertEquals(59.97, $result);
  }
}