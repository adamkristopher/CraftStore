<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use CraftShop\Core\Router;

class RouterTest extends TestCase
{
  private Router $router;

  protected function setUp(): void
  {
    $this->router = new Router();
  }

  public function test_can_register_get_route(): void
  {
    $this->router->get('/products', 'ProductController@index');

    $route = $this->router->resolve('GET', '/products');

    $this->assertEquals('ProductController', $route['controller']);
    $this->assertEquals('index', $route['action']);
  }

  public function test_can_register_post_route(): void
  {
    $this->router->post('/cart/add', 'CartController@add');

    $route = $this->router->resolve('POST', '/cart/add');

    $this->assertEquals('CartController', $route['controller']);
    $this->assertEquals('add', $route['action']);
  }

  public function test_returns_null_for_undefined_route(): void
  {
    $route = $this->router->resolve('GET', '/undefined');
    
    $this->assertNull($route);
  }

  public function test_can_extract_route_parameters(): void
  {
    $this->router->get('/product/{id}', 'ProductController@show');

    $route = $this->router->resolve('GET', '/product/123');

    $this->assertEquals('ProductController', $route['controller']);
    $this->assertEquals('show', $route['action']);
    $this->assertEquals('123', $route['params']['id']);
  }
}