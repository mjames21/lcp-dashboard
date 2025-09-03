<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationChiefdoms extends Model
{
    use HasFactory;
    protected $table = 'location_chiefdom';
    protected $fillable = ['id','idregion','iddistrict','idcouncil','chiefdomname','idemis_code','sl_stats_chiefdom_code'];
   
    
    public function district()
    {
    	return $this->belongsTo(LocationDistricts::class, 'iddistrict', 'id');
    }  
    
     public function council()
    {
    	return $this->belongsTo(LocationCouncils::class, 'idcouncil', 'id');
    }  
    
    
     public function schools()
    {
    	return $this->hasMany(SchoolData::class, 'idchiefdom', 'id');
    }   
     // Define the many-to-many relationship with Clusters
     public function clusters()
    {
        return $this->belongsToMany(Cluster::class, 'chiefdom_cluster', 'chiefdom_id', 'cluster_id');
    }
}
