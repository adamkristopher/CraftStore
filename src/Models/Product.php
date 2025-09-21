<?php

namespace CraftShop\Models;

use CraftShop\Exceptions\InvalidPriceException;

class Product
{
    /**
     * PHP 8 Constructor Property Promotion
     * This declares AND assigns properties in one line
     */
    public function __construct(
        private string $sku,
        private string $name,
        private float $price
    ) {
        // Validate price cannot be negative
        if ($price < 0) {
            throw new InvalidPriceException('Product price cannot be negative');
        }
    }
    
    public function getSku(): string
    {
        return $this->sku;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getPrice(): float
    {
        return $this->price;
    }
    
    public function getDiscountedPrice(float $percentOff): float
    {
        // Validate discount percentage
        if ($percentOff < 0 || $percentOff > 100) {
            throw new \InvalidArgumentException('Discount percentage must be between 0 and 100');
        }
        
        // Calculate discounted price
        $discount = $this->price * ($percentOff / 100);
        return $this->price - $discount;
    }
    
    public function getSlug(): string
    {
        // Convert name to URL-friendly slug
        $slug = strtolower($this->name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
}
