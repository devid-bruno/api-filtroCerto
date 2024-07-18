<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Query extends Model
{
    protected $fillable = ['user_id', 'type', 'query_count'];
    use HasFactory;
}
