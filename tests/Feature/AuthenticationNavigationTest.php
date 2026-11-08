<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sends guests to the login page and protects authenticated routes', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('إدارة العقود')
        ->assertDontSee('تسجيل الخروج');

    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('redirects authenticated users from the root and login page to the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertRedirect(route('dashboard'));

    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirect(route('dashboard'));

    $response = $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('تسجيل الخروج')
        ->assertSee('<meta name="turbo-cache-control" content="no-cache">', false);

    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});

it('keeps a valid login across requests and clears access on logout', function () {
    $user = User::factory()->create();

    $this->post(route('login.post'), [
        'email' => $user->email,
        'password' => 'password',
        'remember' => true,
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);

    $this->get('/')
        ->assertRedirect(route('dashboard'));

    $this->get(route('dashboard'))
        ->assertOk();

    $this->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();

    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});
