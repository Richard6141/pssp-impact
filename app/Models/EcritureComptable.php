<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EcritureComptable extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ecritures_comptables';
    protected $primaryKey = 'ecriture_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ecriture_id',
        'date_ecriture',
        'numero_piece',
        'type_piece',
        'piece_id',
        'compte_debit',
        'compte_credit',
        'libelle',
        'montant',
        'devise',
        'user_id',
    ];

    protected $casts = [
        'date_ecriture' => 'date',
        'montant' => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
