<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Profiles extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'profiles';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'full_name',
        'photo_url',
        'phone',
        'whatsapp',
        'is_active',
        'nik',
        'nim',
        'program_studi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
