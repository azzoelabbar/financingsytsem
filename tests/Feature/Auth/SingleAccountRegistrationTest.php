<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('a fresh install sends the first visitor to the registration form', function () {
    $this->get(route('home'))->assertRedirect(route('register'));
});

test('the landing page replaces the registration call to action once the account exists', function () {
    User::factory()->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee(route('register'));
});

test('the registration form closes once the account exists', function () {
    User::factory()->create();

    $this->get(route('register'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', __('erp.auth.registration_closed'));
});

test('a second account cannot be registered', function () {
    User::factory()->create();

    $this->post(route('register.store'), [
        'name' => 'Second User',
        'email' => 'second@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('login'));

    $this->assertGuest();
    expect(User::count())->toBe(1);
});

test('the login page hides the registration link once the account exists', function () {
    User::factory()->create();

    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee(route('register'));
});
