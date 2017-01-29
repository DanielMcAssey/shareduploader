<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailChange extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'location'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Generate a token for email change
     *
     * @return String
     */
    public function generateToken()
    {
        $this->token = md5(uniqid(mt_rand(), true));
        $this->save();
        return $this->token;
    }
}
