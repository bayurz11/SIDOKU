<?php

namespace App\Shared\Traits;

use App\Shared\Services\CacheService;
use Illuminate\Support\Collection;

trait HasPermissions
{
    public function hasPermission(string $permission): bool
    {
        return $this->getCachedPermissions()->contains('name', $permission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return $this->getCachedPermissions()->pluck('name')->intersect($permissions)->isNotEmpty();
    }

    public function hasAllPermissions(array $permissions): bool
    {
        return empty(array_diff($permissions, $this->getCachedPermissions()->pluck('name')->all()));
    }

    public function getPermissions(): array
    {
        return $this->getCachedPermissions()
            ->pluck('name')
            ->toArray();
    }

    public function canAccessResource(string $resource, string $action): bool
    {
        $permission = "{$resource}.{$action}";

        return $this->hasPermission($permission);
    }

    protected function getCachedPermissions(): Collection
    {
        if (! $this->exists) {
            return collect();
        }

        return CacheService::getUserPermissions($this->getKey());
    }
}
