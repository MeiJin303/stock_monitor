<?php

namespace App\Http\Models;

use App\Http\Classes\ModelObject;

class Stock extends ModelObject   {

    protected $appends = ['change', 'change_percentage'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getChangeAttribute() {
        return round($this->price - $this->previous_close, 3);
    }

    public function getChangePercentageAttribute() {
        return round($this->change/$this->price*100, 3)."%";
    }
}
