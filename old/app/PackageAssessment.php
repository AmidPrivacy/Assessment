<?php

namespace App;

use Illuminate\database\Eloquent\Model;

class PackageAssessment extends Model
{
    protected $table = "package_assessment";
    public $timestamps = false;
    protected $dateFormat = 'd.m.Y H:i';
}
