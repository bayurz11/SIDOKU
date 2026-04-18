<?php

namespace App\Shared\Traits;

use App\Domains\Role\Models\Role;
use App\Shared\Services\CacheService;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function hasRole($role): bool
    {
        $roles = $this->getCachedRoles();

        if (is_string($role)) {
            return $roles->contains('name', $role);
        }

        if (is_array($role)) {
            return $roles->pluck('name')->intersect($role)->isNotEmpty();
        }

        return $role && $roles->contains('id', $role->id);
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->getCachedRoles()->pluck('name')->intersect($roles)->isNotEmpty();
    }

    public function hasAllRoles(array $roles): bool
    {
        return empty(array_diff($roles, $this->getCachedRoles()->pluck('name')->all()));
    }

    public function assignRole($role): self
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        if ($role) {
            $this->roles()->syncWithoutDetaching([$role->id]);
            $this->clearCachedRoles();
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
            $this->clearCachedRoles();
        }

        return $this;
    }

    public function syncRoles(array $roles): self
    {
        $roleIds = Role::whereIn('name', $roles)->pluck('id')->toArray();
        $this->roles()->sync($roleIds);
        $this->clearCachedRoles();

        return $this;
    }

    protected function getCachedRoles(): Collection
    {
        if (! $this->exists) {
            return collect();
        }

        if ($this->relationLoaded('roles')) {
            return $this->getRelation('roles');
        }

        $roles = CacheService::getUserRoles($this->getKey());
        $this->setRelation('roles', $roles);

        return $roles;
    }

    protected function clearCachedRoles(): void
    {
        if (! $this->exists) {
            return;
        }

        CacheService::clearUserCache($this->getKey());
        $this->unsetRelation('roles');
    }
}
