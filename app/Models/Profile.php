<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    public function user()
{
    return $this->belongsTo(User::class);
}

public function educations()
{
    return $this->hasMany(Education::class);
}

public function careers()
{
    return $this->hasMany(Career::class);
}

public function family()
{
    return $this->hasOne(FamilyDetail::class);
}

public function location()
{
    return $this->hasOne(Location::class);
}

public function lifestyle()
{
    return $this->hasOne(Lifestyle::class);
}

public function partnerPreference()
{
    return $this->hasOne(PartnerPreference::class);
}



}
