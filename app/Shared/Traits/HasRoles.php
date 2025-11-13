<?php

namespace App\Shared\Traits;

use App\Domains\Role\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles()->where('roles.name', $role)->exists();
        }

        if (is_array($role)) {
            return $this->roles()->whereIn('roles.name', $role)->exists();
        }

        return $this->roles()->where('roles.id', $role->id)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('roles.name', $roles)->exists();
    }

    public function hasAllRoles(array $roles): bool
    {
        return $this->roles()->whereIn('roles.name', $roles)->count() === count($roles);
    }

    public function assignRole($role): self
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        if ($role) {
            // Check if role is already assigned
            $existingRole = $this->roles()->where('role_id', $role->id)->first();
            if (!$existingRole) {
                $this->roles()->attach($role->id);
            }
        }

        return $this;
    }

    public function removeRole($role): self
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        if ($role) {
            $this->roles()->detach($role->id);
        }

        return $this;
    }

    public function syncRoles(array $roles): self
    {
        $roleIds = Role::whereIn('name', $roles)->pluck('id')->toArray();
        $this->roles()->sync($roleIds);

        return $this;
    }
}