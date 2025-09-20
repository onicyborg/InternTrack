<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Logbooks extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'logbooks';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'subject',
        'description',
        'start_date',
        'end_date',
        'approval_dosen',
        'approval_pembina',
        'remark_dosen',
        'remark_pembina',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attachments()
    {
        return $this->hasMany(LogbooksAttachments::class, 'logbook_id');
    }
}
