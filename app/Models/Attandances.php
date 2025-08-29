<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Attandances extends Model
{
    use HasFactory, HasUuids;

    // Fix table name to match migration
    protected $table = 'attendances';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'checkin_at',
        'checkout_at',
        'photo_checkin_url',
        'photo_checkout_url',
        'ttd_checkin_url',
        'ttd_checkout_url',
        'is_approve_dosen',
        'is_approve_pembina',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
