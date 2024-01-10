<?php

namespace App\Models; 

use Illuminate\database\Eloquent\Model;

class Complaint extends Model
{
    protected $table = "complaint_criterias";
    public $timestamps = false;
    protected $dateFormat = 'd.m.Y H:i';
}
