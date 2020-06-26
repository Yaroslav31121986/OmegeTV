<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Rates;

class Company extends Model
{
    protected $table = 'the_company';

    public function rates() {
        return $this->hasMany(Rates::class, "company_id", "id");
    }

    public function customer() {
        return $this->hasManyThrough(Customer::class, Rates::class,
            "company_id", "rates_id", "id");
    }
}
