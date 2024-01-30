<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_register()
    {
        Artisan::call('migrate');
        $user = [
            'name' => 'Abelardo',
            'last_name' => 'García',
            'email' => 'abelardoreyes256@gmail.com',
            'password' => 'AbelardoGR256',
            'password_confirmation' => 'AbelardoGR256',
            'phone' => '8714733996',
            'captcha' => '03AHaCkA',
        ];
        $response = $this->post(route('register'), $user);
        $response->assertStatus(200);
    }
}
