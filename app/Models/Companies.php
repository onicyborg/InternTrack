<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Companies extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'companies';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_perusahaan',
        'contact_person',
        'email_perusahaan',
        'alamat_perusahaan',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }
}
