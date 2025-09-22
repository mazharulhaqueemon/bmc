<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{

    use HasFactory;
    protected $fillable = [
        'profile_id',
        'Heighets_degree',
        'institute_name',
        'graduation_year',
        'additional_certificates',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
