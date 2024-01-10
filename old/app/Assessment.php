<?php

namespace App;

use Illuminate\database\Eloquent\Model;

class Assessment extends Model
{
    protected $table = "assessment";
    public $timestamps = false;
    protected $dateFormat = 'd.m.Y H:i';
}
