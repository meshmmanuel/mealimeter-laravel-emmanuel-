<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    public function user()
    {
        return $this->hasOne('App\User');
    }

    public function role()
    {
        return $this->hasOne('App\Role');
    }
}
