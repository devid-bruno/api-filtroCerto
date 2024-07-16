<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadType extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_name',
    ];

    public const TIPO_PORTABILIDADE = 1;
    public const TIPO_WHATSAPP = 2;
    public const TIPO_INCLUSAO = 3;

    public function uploads()
    {
        return $this->hasMany(Upload::class);
    }
}
