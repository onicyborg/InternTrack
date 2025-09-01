<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role',
        'email',
        'password',
        'is_active',
        'remember_token',
        'dosen_user_id',
        'pembina_user_id',
        'campus_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    // Relationships
    public function profile()
    {
        return $this->hasOne(Profiles::class, 'user_id');
    }

    public function campus()
    {
        return $this->belongsTo(Campuses::class, 'campus_id');
    }

    // Self-referential relations (for mahasiswa linking to dosen/pembina)
    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_user_id');
    }

    public function pembina()
    {
        return $this->belongsTo(User::class, 'pembina_user_id');
    }

    // Reverse: list of mahasiswa dibimbing oleh dosen/pembina ini
    public function mahasiswaBimbinganDosen()
    {
        return $this->hasMany(User::class, 'dosen_user_id');
    }

    public function mahasiswaBimbinganPembina()
    {
        return $this->hasMany(User::class, 'pembina_user_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attandances::class, 'user_id');
    }

    public function logbooks()
    {
        return $this->hasMany(Logbooks::class, 'user_id');
    }
}

