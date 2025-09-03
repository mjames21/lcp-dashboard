<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationCouncils extends Model
{
    use HasFactory;
     use HasFactory;
    protected $table = 'location_councils';
    protected $fillable = ['id','councilname'];
  
    
     public function chiefdom()
    {
    	return $this->hasMany(LocationChiefdoms::class, 'idcouncil', 'id');
    }   
}
