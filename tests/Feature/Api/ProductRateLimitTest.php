<?php

use App\Models\Product;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    // Clear rate limiter state before each test
    RateLimiter::clear('api:127.0.0.1');
});

it('allows requests within rate limit', function () {
    Product::factory()->create();

    // Make requests within the rate limit (60 per minute)
    for ($i = 0; $i < 5; $i++) {
        $response = $this->getJson('/api/v1/products');
        $response->assertStatus(200);
    }
});

it('blocks requests when rate limit is exceeded', function () {
    Product::factory()->create();

    // Make requests up to the rate limit
    for ($i = 0; $i < 60; $i++) {
        $this->getJson('/api/v1/products');
    }

    // The 61st request should be rate limited
    $response = $this->getJson('/api/v1/products');
    
    $response->assertStatus(429); // Too Many Requests
    $response->assertJson([
        'message' => 'Too Many Attempts.'
    ]);
});

it('includes rate limit headers in response', function () {
    Product::factory()->create();

    $response = $this->getJson('/api/v1/products');
    
    $response->assertStatus(200);
    $response->assertHeader('X-RateLimit-Limit', '60');
    $response->assertHeader('X-RateLimit-Remaining');
});

it('resets rate limit after time window', function () {
    Product::factory()->create();

    // Exhaust the rate limit
    for ($i = 0; $i < 60; $i++) {
        $this->getJson('/api/v1/products');
    }

    // Should be rate limited
    $response = $this->getJson('/api/v1/products');
    $response->assertStatus(429);

    // Travel forward in time by 61 seconds to reset the rate limit window
    $this->travel(61)->seconds();

    // Should work again after time window reset
    $response = $this->getJson('/api/v1/products');
    $response->assertStatus(200);
});

it('rate limits by IP address for unauthenticated users', function () {
    Product::factory()->create();

    // Make requests from different IP addresses
    $response1 = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.1'])
        ->getJson('/api/v1/products');
    
    $response2 = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.2'])
        ->getJson('/api/v1/products');

    $response1->assertStatus(200);
    $response2->assertStatus(200);
});