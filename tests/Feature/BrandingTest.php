<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// The landing page only renders once the install has its account; before that
// the first visitor is sent to the one-time registration form.
beforeEach(function () {
    User::factory()->create();
});

test('public pages use the Mizan identity and favicon assets', function () {
    $response = $this->withSession(['locale' => 'en'])->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Mizan')
        ->assertSee('brand/mizan-mark.png', false)
        ->assertSee('favicon.ico', false)
        ->assertSee('site.webmanifest', false);
});

test('the Arabic interface uses the Mizan project name', function () {
    $this->withSession(['locale' => 'ar'])
        ->get(route('home'))
        ->assertOk()
        ->assertSee('ميزان')
        ->assertSee('dir="rtl"', false);
});

test('required Mizan image assets exist', function () {
    expect(config('app.name'))->toBe('ميزان')
        ->and(public_path('brand/mizan-logo.webp'))->toBeFile()
        ->and(public_path('brand/mizan-logo.png'))->toBeFile()
        ->and(public_path('brand/mizan-mark.png'))->toBeFile()
        ->and(public_path('favicon.ico'))->toBeFile()
        ->and(public_path('apple-touch-icon.png'))->toBeFile();
});
