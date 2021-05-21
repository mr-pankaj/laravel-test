<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

class Workshop extends Model
{
    public function event()
    {
        return $this->hasOne(Event::class);
    }
}
