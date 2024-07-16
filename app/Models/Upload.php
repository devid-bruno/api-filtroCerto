<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'uuid', 'status', 'upload_type_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function uploadType()
    {
        return $this->belongsTo(UploadType::class);
    }
    
}
