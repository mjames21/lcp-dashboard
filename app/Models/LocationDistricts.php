<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocationDistricts extends Model
{
    use HasFactory;
    protected $table = 'location_districts';
    protected $fillable = ['id','districtname'];
    
    public function chiefdoms()
    {
    	return $this->hasMany(LocationChiefdoms::class, 'iddistrict','id');
    }
    
     public function region()
    {
    	return $this->belongsTo(LocationRegions::class, 'idregion','id');
    }
     
}
