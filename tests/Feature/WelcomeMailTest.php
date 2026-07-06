<?php

namespace Tests\Feature;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WelcomeMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_self_registration_sends_welcome_mail(): void
    {
        Mail::fake();

        $response = $this->post(route('register.store'), [
            'firstname' => 'Awa',
            'lastname' => 'Kossou',
            'username' => 'awa.kossou',
            'email' => 'awa.kossou@exemple.com',
            'password' => 'MotDePasse123',
            'password_confirmation' => 'MotDePasse123',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', ['email' => 'awa.kossou@exemple.com']);

        Mail::assertSent(WelcomeMail::class, function ($mail) {
            return $mail->hasTo('awa.kossou@exemple.com');
        });
    }

    public function test_registration_succeeds_even_if_mail_fails(): void
    {
        // Simule un SMTP en panne : l'inscription ne doit pas échouer pour autant
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP indisponible'));

        $response = $this->post(route('register.store'), [
            'firstname' => 'Koffi',
            'lastname' => 'Mensah',
            'username' => 'koffi.mensah',
            'email' => 'koffi.mensah@exemple.com',
            'password' => 'MotDePasse123',
            'password_confirmation' => 'MotDePasse123',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', ['email' => 'koffi.mensah@exemple.com']);
        $this->assertNotNull(User::where('email', 'koffi.mensah@exemple.com')->first());
    }
}
