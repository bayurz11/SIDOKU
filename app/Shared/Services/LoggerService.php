<?php

namespace App\Shared\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class LoggerService
{
    const LEVEL_EMERGENCY = 'emergency';
    const LEVEL_ALERT = 'alert';
    const LEVEL_CRITICAL = 'critical';
    const LEVEL_ERROR = 'error';
    const LEVEL_WARNING = 'warning';
    const LEVEL_NOTICE = 'notice';
    const LEVEL_INFO = 'info';
    const LEVEL_DEBUG = 'debug';

    protected static function getContext(): array
    {
        return [
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
        ];
    }

    public static function logUserAction(string $action, string $entity, $entityId = null, array $data = [], string $level = self::LEVEL_INFO)
    {
        $context = array_merge(self::getContext(), [
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'data' => $data,
            'category' => 'user_action'
        ]);

        Log::log($level, "User action: {$action} on {$entity}" . ($entityId ? " (ID: {$entityId})" : ''), $context);
    }

    public static function logSecurityEvent(string $event, string $level = self::LEVEL_WARNING, array $data = [])
    {
        $context = array_merge(self::getContext(), [
            'event' => $event,
            'data' => $data,
            'category' => 'security'
        ]);

        Log::log($level, "Security event: {$event}", $context);
    }

    public static function logSystemEvent(string $event, string $level = self::LEVEL_INFO, array $data = [])
    {
        $context = array_merge(self::getContext(), [
            'event' => $event,
            'data' => $data,
            'category' => 'system'
        ]);

        Log::log($level, "System event: {$event}", $context);
    }

    public static function logDatabaseOperation(string $operation, string $table, $recordId = null, array $data = [], string $level = self::LEVEL_INFO)
    {
        $context = array_merge(self::getContext(), [
            'operation' => $operation,
            'table' => $table,
            'record_id' => $recordId,
            'data' => $data,
            'category' => 'database'
        ]);

        Log::log($level, "Database operation: {$operation} on {$table}" . ($recordId ? " (ID: {$recordId})" : ''), $context);
    }

    public static function logAPIRequest(string $endpoint, string $method, array $data = [], string $level = self::LEVEL_INFO)
    {
        $context = array_merge(self::getContext(), [
            'endpoint' => $endpoint,
            'api_method' => $method,
            'request_data' => $data,
            'category' => 'api'
        ]);

        Log::log($level, "API request: {$method} {$endpoint}", $context);
    }

    public static function logPerformance(string $operation, float $executionTime, array $data = [])
    {
        $context = array_merge(self::getContext(), [
            'operation' => $operation,
            'execution_time' => $executionTime,
            'data' => $data,
            'category' => 'performance'
        ]);

        $level = $executionTime > 5 ? self::LEVEL_WARNING : self::LEVEL_INFO;
        
        Log::log($level, "Performance: {$operation} took {$executionTime}s", $context);
    }

    public static function logAuthentication(string $event, ?string $email = null, string $level = self::LEVEL_INFO, array $data = [])
    {
        $context = array_merge(self::getContext(), [
            'auth_event' => $event,
            'email' => $email ?? Auth::user()?->email,
            'data' => $data,
            'category' => 'authentication'
        ]);

        Log::log($level, "Authentication: {$event}" . ($email ? " for {$email}" : ''), $context);
    }

    public static function logError(\Throwable $exception, array $data = [])
    {
        $context = array_merge(self::getContext(), [
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_code' => $exception->getCode(),
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
            'exception_trace' => $exception->getTraceAsString(),
            'data' => $data,
            'category' => 'error'
        ]);

        Log::error("Exception: {$exception->getMessage()}", $context);
    }

    public static function emergency(string $message, array $context = [])
    {
        Log::emergency($message, array_merge(self::getContext(), $context));
    }

    public static function alert(string $message, array $context = [])
    {
        Log::alert($message, array_merge(self::getContext(), $context));
    }

    public static function critical(string $message, array $context = [])
    {
        Log::critical($message, array_merge(self::getContext(), $context));
    }

    public static function error(string $message, array $context = [])
    {
        Log::error($message, array_merge(self::getContext(), $context));
    }

    public static function warning(string $message, array $context = [])
    {
        Log::warning($message, array_merge(self::getContext(), $context));
    }

    public static function notice(string $message, array $context = [])
    {
        Log::notice($message, array_merge(self::getContext(), $context));
    }

    public static function info(string $message, array $context = [])
    {
        Log::info($message, array_merge(self::getContext(), $context));
    }

    public static function debug(string $message, array $context = [])
    {
        Log::debug($message, array_merge(self::getContext(), $context));
    }

    // Query logging methods
    public static function logSlowQuery(string $sql, array $bindings, float $time)
    {
        if ($time > config('logging.slow_query_threshold', 1000)) {
            self::warning("Slow query detected", [
                'sql' => $sql,
                'bindings' => $bindings,
                'execution_time' => $time,
                'category' => 'slow_query'
            ]);
        }
    }

    // Cache operations logging
    public static function logCacheOperation(string $operation, string $key, $ttl = null)
    {
        self::debug("Cache operation: {$operation}", [
            'operation' => $operation,
            'key' => $key,
            'ttl' => $ttl,
            'category' => 'cache'
        ]);
    }
}