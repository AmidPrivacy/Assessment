<?php

namespace App;

use Illuminate\database\Eloquent\Model;

class UserReport extends Model
{
    protected $table = "user_report";
    public $timestamps = false;
    protected $dateFormat = 'd.m.Y H:i';
}
