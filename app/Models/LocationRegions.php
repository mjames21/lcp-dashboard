<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationRegions extends Model
{
    use HasFactory;
    protected $table = 'location_regions';
    protected $fillable = ['id','regionname'];
    
    //distrcits
    public function districts()
    {
    	return $this->hasMany(LocationDistrict::class, 'idregion','id');
    }
}
