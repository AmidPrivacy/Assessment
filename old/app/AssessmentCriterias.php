<?php

namespace App;

use Illuminate\database\Eloquent\Model;

class AssessmentCriterias extends Model
{
    protected $fillable = ['assessment_id','call_id','count','criterias'];
    protected $table = "assessment_criterias";
    public $timestamps = false;
    protected $dateFormat = 'd.m.Y H:i';
}
