<?php

namespace App\Shared\Traits;

trait HasPermissions
{
    public function hasPermission(string $permission): bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('permissions.name', $permission)->where('permissions.is_active', true);
        })->exists();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permissions) {
            $query->whereIn('permissions.name', $permissions)->where('permissions.is_active', true);
        })->exists();
    }

    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    public function getPermissions(): array
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->where('is_active', true)
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    }

    public function canAccessResource(string $resource, string $action): bool
    {
        $permission = "{$resource}.{$action}";
        return $this->hasPermission($permission);
    }
}