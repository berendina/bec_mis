<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

use OwenIt\Auditing\Contracts\Auditable;

class Role extends Model implements Auditable
{
    
    use \OwenIt\Auditing\Auditable;
    public $timestamps = true;
    protected $fillable=[ 'name','slug','permissions'];

    public function users()
    {
    	return $this->belongsToMany(User::class,'roles_users');
    }

    public function hasAccess(array $permissions)
    {
       foreach($permissions as $permission){
            if($this->hasPermission($permission)){
                return $permission;
            }
       }
       return false;
    }
    protected function hasPermission(string $permission)
    {
    	$permissions= json_decode($this->permissions,true);
    	return $permissions[$permission]??false;
    }
}
 