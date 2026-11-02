<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role', 'is_active', 'permissions',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];

    public function permissionMatrix()
    {
        $modules = config('erp.permission_modules', []);
        $permissions = [];

        foreach ($modules as $module => $actions) {
            if ($this->role === 'admin') {
                $permissions[$module] = $actions;
            } elseif (is_array($this->permissions)) {
                $permissions[$module] = array_values(array_intersect(
                    $this->permissions[$module] ?? [],
                    $actions
                ));
            } else {
                $permissions[$module] = array_values(array_intersect(
                    config('erp.default_permissions.' . $module, []),
                    $actions
                ));
            }
        }

        return $permissions;
    }

    public function canAccess($module, $action)
    {
        return $this->role === 'admin'
            || in_array($action, $this->permissionMatrix()[$module] ?? [], true);
    }

    public function erpRecords()
    {
        return $this->hasMany(ErpRecord::class, 'created_by');
    }
}
