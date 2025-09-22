<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class FamilyDetail extends Model
{   use HasFactory;
    protected $fillable = [
        'profile_id',
        'father_name',
        'mother_name',
        'siblings_count',
        'family_type',
        'family_values',
        'about_family',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
