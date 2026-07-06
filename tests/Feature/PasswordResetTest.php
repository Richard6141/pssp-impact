<?php

namespace Tests\Feature;

use App\Mail\PasswordResetMail;
use App\Models\User;
use App\Services\PasswordResetService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_link_stores_hashed_token_and_sends_mail(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->post(route('password.email'), ['email' => $user->email]);

        $response->assertSessionHas('status');

        Mail::assertSent(PasswordResetMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $record = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $this->assertNotNull($record);
        // Le token en base est un hash SHA-256 (64 hex), jamais le token en clair
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $record->token);
    }

    public function test_unknown_email_gets_same_generic_response_without_mail(): void
    {
        Mail::fake();

        $response = $this->post(route('password.email'), ['email' => 'inconnu@exemple.com']);

        // Anti-énumération : même message que pour un compte existant
        $response->assertSessionHas('status');
        $response->assertSessionHasNoErrors();

        Mail::assertNothingSent();
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'inconnu@exemple.com']);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();
        $plainToken = PasswordResetService::createToken($user->email);

        $response = $this->post(route('password.update'), [
            'token' => $plainToken,
            'email' => $user->email,
            'password' => 'NouveauMotDePasse1',
            'password_confirmation' => 'NouveauMotDePasse1',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('NouveauMotDePasse1', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_expired_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $plainToken = PasswordResetService::createToken($user->email);

        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->update(['created_at' => Carbon::now()->subMinutes(PasswordResetService::EXPIRATION_MINUTES + 5)]);

        $response = $this->post(route('password.update'), [
            'token' => $plainToken,
            'email' => $user->email,
            'password' => 'NouveauMotDePasse1',
            'password_confirmation' => 'NouveauMotDePasse1',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertFalse(Hash::check('NouveauMotDePasse1', $user->fresh()->password));
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();
        PasswordResetService::createToken($user->email);

        $response = $this->post(route('password.update'), [
            'token' => 'token-falsifie',
            'email' => $user->email,
            'password' => 'NouveauMotDePasse1',
            'password_confirmation' => 'NouveauMotDePasse1',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertFalse(Hash::check('NouveauMotDePasse1', $user->fresh()->password));
    }
}
