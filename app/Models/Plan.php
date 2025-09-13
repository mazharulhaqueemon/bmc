<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model{
    use HasFactory;
    protected $fillable = [
        'plan_name',             
        'description',            
        'profile_picture_limit',  
        'phone_request_limit',    
        'chat_duration_days',     
    ];
}
