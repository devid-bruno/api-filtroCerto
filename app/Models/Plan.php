<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['name', 'portability_limit', 'whatsapp_limit', 'price_per_100k'];
    use HasFactory;
}
