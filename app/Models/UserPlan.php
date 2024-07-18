<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPlan extends Model
{
    protected $fillable = ['user_id', 'plan_id', 'whatsapp_queries_remaining'];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
    
    public function deductWhatsappQueries($count)
    {
        if ($this->whatsapp_queries_remaining >= $count) {
            $this->whatsapp_queries_remaining -= $count;
            $this->save();
            return true;
        }
        return false;
    }
}
