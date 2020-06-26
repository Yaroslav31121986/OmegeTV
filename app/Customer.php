<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';

    public function rates() {
        return $this->belongsTo(Rates::class, "rates_id", "id");
    }
}
