<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LogbooksAttachments extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'logbook_attachments';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'logbook_id',
        'filename',
    ];

    public function logbook()
    {
        return $this->belongsTo(Logbooks::class, 'logbook_id');
    }
}
