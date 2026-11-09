<?php

namespace Tests\Feature;

class LoginTest extends PosTestCase
{
    public function test_halaman_dashboard_butuh_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_login_berhasil_dan_salah_password(): void
    {
        $this->post('/login', ['email' => 'owner@test.com', 'password' => 'salah'])
            ->assertSessionHas('error');

        $this->post('/login', ['email' => 'owner@test.com', 'password' => 'password'])
            ->assertRedirect('/dashboard');
    }

    public function test_kasir_tidak_boleh_masuk_menu_owner(): void
    {
        $this->actingAs($this->cashier)->get('/reports')->assertStatus(403);
        $this->actingAs($this->cashier)->get('/stock-transfer')->assertStatus(403);
        $this->actingAs($this->owner)->get('/reports')->assertOk();
    }
}
