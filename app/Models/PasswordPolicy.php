<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordPolicy extends Model
{
    protected $fillable = [
        'min_length',
        'require_uppercase',
        'require_lowercase',
        'require_numbers',
        'require_special_chars',
        'password_expiry_days',
        'password_history_count',
        'max_login_attempts',
        'lockout_duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'require_uppercase' => 'boolean',
        'require_lowercase' => 'boolean',
        'require_numbers' => 'boolean',
        'require_special_chars' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Récupérer la politique active
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Valider un mot de passe selon la politique
     */
    public function validate(string $password): array
    {
        $errors = [];

        if (strlen($password) < $this->min_length) {
            $errors[] = "Le mot de passe doit contenir au moins {$this->min_length} caractères.";
        }

        if ($this->require_uppercase && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une lettre majuscule.";
        }

        if ($this->require_lowercase && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une lettre minuscule.";
        }

        if ($this->require_numbers && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
        }

        if ($this->require_special_chars && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un caractère spécial.";
        }

        return $errors;
    }
}
