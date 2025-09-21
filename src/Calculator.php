<?php

namespace CraftShop;

class Calculator
{
  public function add(float $a, float $b): float
  {
    return $a + $b;
  }

  public function subtract(float $a, float $b): float
  {
    return $a - $b;
  }

  public function multiply(float $a, float $b): float
  {
    return $a * $b;
  }

   public function percentage(float $subtotal, float $taxRate): float
  {
    $taxes = $subtotal * ($taxRate / 100);
    return $subtotal + $taxes; 
  }
}