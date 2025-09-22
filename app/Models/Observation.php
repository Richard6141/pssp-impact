<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Observation extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $primaryKey = 'observation_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'site_id',
        'user_id',
        'contenu',
        'date_obs',
    ];

    // Relations
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
