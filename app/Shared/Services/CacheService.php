<?php

namespace App\Shared\Services;

use App\Domains\Department\Models\Department;
use App\Domains\Document\Models\Document;
use App\Domains\Ipc\Models\IpcProductCheck;
use App\Domains\Permission\Models\Permission;
use App\Domains\Role\Models\Role;
use App\Domains\User\Models\User;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheService
{
    public const DEFAULT_TTL = 3600; // 1 hour
    public const LONG_TTL = 86400; // 24 hours
    public const SHORT_TTL = 300; // 5 minutes

    private const ACCESS_VERSION_KEY = 'cache.access.version';
    private const DASHBOARD_STATS_KEY = 'dashboard.stats';

    public static function getUserRoles($userId, $ttl = self::DEFAULT_TTL)
    {
        return Cache::remember(self::userScopedKey($userId, 'roles'), $ttl, function () use ($userId) {
            $user = User::find($userId);

            return $user?->roles()->with('permissions')->get() ?? collect();
        });
    }

    public static function getUserPermissions($userId, $ttl = self::DEFAULT_TTL)
    {
        return Cache::remember(self::userScopedKey($userId, 'permissions'), $ttl, function () use ($userId, $ttl) {
            return self::getUserRoles($userId, $ttl)
                ->pluck('permissions')
                ->flatten()
                ->where('is_active', true)
                ->unique('id')
                ->values();
        });
    }

    public static function getRolePermissions($roleId, $ttl = self::DEFAULT_TTL)
    {
        return Cache::remember(self::roleScopedKey($roleId, 'permissions'), $ttl, function () use ($roleId) {
            $role = Role::find($roleId);

            return $role?->permissions()->where('permissions.is_active', true)->get() ?? collect();
        });
    }

    public static function getActiveRoles($ttl = self::LONG_TTL)
    {
        return Cache::remember(self::systemScopedKey('roles.active'), $ttl, function () {
            return Role::where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    public static function getActivePermissions($ttl = self::LONG_TTL)
    {
        return Cache::remember(self::systemScopedKey('permissions.active'), $ttl, function () {
            return Permission::where('is_active', true)
                ->orderBy('group')
                ->orderBy('name')
                ->get();
        });
    }

    public static function getPermissionsByGroup($ttl = self::LONG_TTL)
    {
        return Cache::remember(self::systemScopedKey('permissions.by_group'), $ttl, function () {
            return Permission::where('is_active', true)
                ->orderBy('group')
                ->orderBy('name')
                ->get()
                ->groupBy('group');
        });
    }

    public static function clearUserCache($userId)
    {
        self::bumpVersion(self::userVersionKey($userId));
    }
    public static function clearDepartmentCache($departmentId)
    {
        Cache::forget("department.{$departmentId}.roles");
        Cache::forget("department.{$departmentId}.permissions");
    }

    public static function clearRoleCache($roleId)
    {
        self::bumpVersion(self::roleVersionKey($roleId));
        self::clearSystemCache();
    }

    public static function clearSystemCache()
    {
        self::bumpVersion(self::ACCESS_VERSION_KEY);
    }

    public static function clearAllUserCaches()
    {
        self::clearSystemCache();
    }


    // ... use lain

    public static function getDashboardStats($ttl = self::SHORT_TTL)
    {
        return Cache::remember(self::DASHBOARD_STATS_KEY, $ttl, function () {
            $departmentStats = Department::query()
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
                ->first();

            $documentStats = Document::query()
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
                ->first();

            $userStats = User::query()
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
                ->first();

            $roleStats = Role::query()
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
                ->first();

            $permissionStats = Permission::query()
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
                ->first();

            $incomingMaterialStats = IncomingMaterial::query()
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted")
                ->selectRaw("SUM(CASE WHEN status = 'hold' THEN 1 ELSE 0 END) as hold")
                ->selectRaw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected")
                ->first();

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

            $totalDepartments = (int) ($departmentStats->total ?? 0);
            $activeDepartments = (int) ($departmentStats->active ?? 0);
            $totalDocuments = (int) ($documentStats->total ?? 0);
            $activeDocuments = (int) ($documentStats->active ?? 0);
            $totalUsers = (int) ($userStats->total ?? 0);
            $activeUsers = (int) ($userStats->active ?? 0);
            $totalRoles = (int) ($roleStats->total ?? 0);
            $activeRoles = (int) ($roleStats->active ?? 0);
            $totalPermissions = (int) ($permissionStats->total ?? 0);

            return [

                // ===== DEPARTEMEN =====
                'total_departments'    => $totalDepartments,
                'active_departments'   => $activeDepartments,
                'inactive_departments' => $totalDepartments - $activeDepartments,

                // ===== USERS =====
                'total_users'    => $totalUsers,
                'active_users'   => $activeUsers,
                'inactive_users' => $totalUsers - $activeUsers,

                // ===== ROLES =====
                'total_roles'    => $totalRoles,
                'active_roles'   => $activeRoles,
                'inactive_roles' => $totalRoles - $activeRoles,

                // ===== PERMISSIONS =====
                'total_permissions'  => $totalPermissions,
                'active_permissions' => (int) ($permissionStats->active ?? 0),

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
                    ->select([
                        'id',
                        'document_code',
                        'title',
                        'document_type_id',
                        'department_id',
                        'is_active',
                        'created_by',
                        'updated_by',
                        'created_at',
                        'updated_at',
                    ])
                    ->orderByDesc('updated_at')
                    ->take(3)
                    ->get(),

                // ===== ARRIVAL OF GOODS =====
                'total_arrival_of_goods'    => (int) ($incomingMaterialStats->total ?? 0),
                'accepted_arrival_of_goods' => (int) ($incomingMaterialStats->accepted ?? 0),
                'hold_arrival_of_goods'     => (int) ($incomingMaterialStats->hold ?? 0),
                'rejected_arrival_of_goods' => (int) ($incomingMaterialStats->rejected ?? 0),

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
            ];
        });
    }


    public static function clearDashboardCache()
    {
        Cache::forget(self::DASHBOARD_STATS_KEY);
    }

    private static function userScopedKey($userId, string $suffix): string
    {
        return sprintf(
            'access:v%s:user:%s:v%s:%s',
            self::getVersion(self::ACCESS_VERSION_KEY),
            $userId,
            self::getVersion(self::userVersionKey($userId)),
            $suffix
        );
    }

    private static function roleScopedKey($roleId, string $suffix): string
    {
        return sprintf(
            'access:v%s:role:%s:v%s:%s',
            self::getVersion(self::ACCESS_VERSION_KEY),
            $roleId,
            self::getVersion(self::roleVersionKey($roleId)),
            $suffix
        );
    }

    private static function systemScopedKey(string $suffix): string
    {
        return sprintf('access:v%s:%s', self::getVersion(self::ACCESS_VERSION_KEY), $suffix);
    }

    private static function userVersionKey($userId): string
    {
        return "cache.user.{$userId}.version";
    }

    private static function roleVersionKey($roleId): string
    {
        return "cache.role.{$roleId}.version";
    }

    private static function getVersion(string $key): int
    {
        return (int) Cache::get($key, 1);
    }

    private static function bumpVersion(string $key): void
    {
        Cache::forever($key, self::getVersion($key) + 1);
    }
}
