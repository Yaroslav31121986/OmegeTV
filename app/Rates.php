<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Rates extends Model
{
    protected $table = 'rates';

    public function company() {
        return $this->belongsTo(Company::class, "company_id", "id");
    }

    public function customer() {
        return $this->hasMany(Customer::class, "rates_id", "id");
    }
}
