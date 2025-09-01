<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campuses extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'campuses';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_campus',
        'contact_person',
        'email_campus',
        'alamat_campus',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'campus_id');
    }
}
