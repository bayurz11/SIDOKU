<?php

namespace App\Shared\Services;

use App\Domains\Document\Models\Document;
use App\Domains\Ipc\Models\IpcProductCheck;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;


class CacheService
{
    const DEFAULT_TTL = 3600; // 1 hour
    const LONG_TTL = 86400; // 24 hours
    const SHORT_TTL = 300; // 5 minutes

    public static function getUserRoles($userId, $ttl = self::DEFAULT_TTL)
    {
        return Cache::remember("user.{$userId}.roles", $ttl, function () use ($userId) {
            return \App\Domains\User\Models\User::find($userId)?->roles()->with('permissions')->get();
        });
    }

    public static function getUserPermissions($userId, $ttl = self::DEFAULT_TTL)
    {
        return Cache::remember("user.{$userId}.permissions", $ttl, function () use ($userId) {
            $user = \App\Domains\User\Models\User::find($userId);
            if (!$user) {
                return collect();
            }

            return $user->roles()
                ->with('permissions')
                ->get()
                ->pluck('permissions')
                ->flatten()
                ->where('is_active', true)
                ->unique('id')
                ->values();
        });
    }

    public static function getRolePermissions($roleId, $ttl = self::DEFAULT_TTL)
    {
        return Cache::remember("role.{$roleId}.permissions", $ttl, function () use ($roleId) {
            return \App\Domains\Role\Models\Role::find($roleId)?->permissions()->where('is_active', true)->get();
        });
    }

    public static function getActiveRoles($ttl = self::LONG_TTL)
    {
        return Cache::remember('roles.active', $ttl, function () {
            return \App\Domains\Role\Models\Role::where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    public static function getActivePermissions($ttl = self::LONG_TTL)
    {
        return Cache::remember('permissions.active', $ttl, function () {
            return \App\Domains\Permission\Models\Permission::where('is_active', true)
                ->orderBy('group')
                ->orderBy('name')
                ->get();
        });
    }

    public static function getPermissionsByGroup($ttl = self::LONG_TTL)
    {
        return Cache::remember('permissions.by_group', $ttl, function () {
            return \App\Domains\Permission\Models\Permission::where('is_active', true)
                ->orderBy('group')
                ->orderBy('name')
                ->get()
                ->groupBy('group');
        });
    }

    public static function clearUserCache($userId)
    {
        Cache::forget("user.{$userId}.roles");
        Cache::forget("user.{$userId}.permissions");
    }
    public static function clearDepartmentCache($departmentId)
    {
        Cache::forget("department.{$departmentId}.roles");
        Cache::forget("department.{$departmentId}.permissions");
    }

    public static function clearRoleCache($roleId)
    {
        Cache::forget("role.{$roleId}.permissions");
        self::clearSystemCache();
    }

    public static function clearSystemCache()
    {
        Cache::forget('roles.active');
        Cache::forget('permissions.active');
        Cache::forget('permissions.by_group');
    }

    public static function clearAllUserCaches()
    {
        $pattern = 'user.*.roles';
        self::clearCacheByPattern($pattern);

        $pattern = 'user.*.permissions';
        self::clearCacheByPattern($pattern);
    }

    private static function clearCacheByPattern($pattern)
    {
        try {
            if (config('cache.default') === 'redis') {
                $keys = Redis::keys(config('cache.prefix') . ':' . $pattern);
                if (!empty($keys)) {
                    Redis::del($keys);
                }
            } else {
                // For other cache drivers, we'll need to clear all cache
                Cache::flush();
            }
        } catch (\Exception $e) {
            // Fallback to cache flush if pattern deletion fails
            Cache::flush();
        }
    }


    // ... use lain

    public static function getDashboardStats($ttl = self::SHORT_TTL)
    {
        return Cache::remember('dashboard.stats', $ttl, function () {

            $totalDepartments  = \App\Domains\Department\Models\Department::count();
            $activeDepartments = \App\Domains\Department\Models\Department::where('is_active', true)->count();

            $totalDocuments  = Document::count();
            $activeDocuments = Document::where('is_active', true)->count();

            // ===== IPC MOISTURE SUMMARY =====
            $moistureSummary = IpcProductCheck::select(
                'line_group',
                'sub_line',
                DB::raw('AVG(avg_moisture_percent) as avg_moisture'),
                DB::raw('COUNT(*) as total_sample')
            )
                ->groupBy('line_group', 'sub_line')
                ->orderBy('line_group')
                ->get();

            $lineLabels = IpcProductCheck::LINE_GROUPS ?? [];
            $subLineLabels = IpcProductCheck::SUB_LINES_TEH ?? [];

            $moistureLabels = $moistureSummary->map(function ($row) use ($lineLabels, $subLineLabels) {

                $lineLabel = $lineLabels[$row->line_group] ?? $row->line_group;
                $subLabel = $row->sub_line ? ($subLineLabels[$row->sub_line] ?? $row->sub_line) : null;

                return $subLabel ?: $lineLabel;
            });

            $moistureValues = $moistureSummary->map(function ($row) {
                return round($row->avg_moisture, 2);
            });

            $moistureCounts = $moistureSummary->pluck('total_sample');


            // ===== MOISTURE ALERT (>=10%) =====
            $highMoistureItems = IpcProductCheck::where('avg_moisture_percent', '>=', 10)
                ->latest('test_date')
                ->take(10)
                ->get();

            return [

                // ===== DEPARTEMEN =====
                'total_departments'    => $totalDepartments,
                'active_departments'   => $activeDepartments,
                'inactive_departments' => $totalDepartments - $activeDepartments,

                // ===== USERS =====
                'total_users'  => \App\Domains\User\Models\User::count(),
                'active_users' => \App\Domains\User\Models\User::where('is_active', true)->count(),

                // ===== ROLES =====
                'total_roles'  => \App\Domains\Role\Models\Role::count(),
                'active_roles' => \App\Domains\Role\Models\Role::where('is_active', true)->count(),

                // ===== PERMISSIONS =====
                'total_permissions' => \App\Domains\Permission\Models\Permission::count(),

                // ===== DOCUMENTS =====
                'total_documents'    => $totalDocuments,
                'active_documents'   => $activeDocuments,
                'inactive_documents' => $totalDocuments - $activeDocuments,

                // ===== RECENT DOCUMENTS =====
                'recent_documents' => Document::with([
                    'documentType',
                    'department',
                    'createdBy',
                    'updatedBy',
                ])
                    ->orderByDesc('updated_at')
                    ->take(3)
                    ->get(),

                // ===== ARRIVAL OF GOODS =====
                'total_arrival_of_goods'   => IncomingMaterial::count(),
                'accepted_arrival_of_goods' => IncomingMaterial::where('status', 'accepted')->count(),
                'hold_arrival_of_goods'    => IncomingMaterial::where('status', 'hold')->count(),
                'rejected_arrival_of_goods' => IncomingMaterial::where('status', 'rejected')->count(),

                // ===== IPC MOISTURE CHART =====
                'ipc_moisture_chart' => [
                    'labels' => $moistureLabels,
                    'values' => $moistureValues,
                    'counts' => $moistureCounts,
                ],

                // ===== IPC MOISTURE ALERT =====
                'ipc_moisture_alert' => [
                    'has_alert' => $highMoistureItems->isNotEmpty(),
                    'items' => $highMoistureItems,
                ],

                // ===== RECENT USERS =====
                'recent_users' => \App\Domains\User\Models\User::latest()->take(5)->get(),
            ];
        });
    }


    public static function clearDashboardCache()
    {
        Cache::forget('dashboard.stats');
    }
}
