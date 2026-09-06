<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a successful response', function () {
    // The landing page only renders once the install has its single account.
    User::factory()->create();

    $response = $this->get('/');

    $response->assertStatus(200);
});
