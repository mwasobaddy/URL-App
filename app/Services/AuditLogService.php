<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AuditLogService
{
    /**
     * Log a user action
     */
    public function logUserAction(
        string $event,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        array $tags = []
    ): void {
        try {
            ActivityLog::log($event, $auditable, $oldValues, $newValues, $tags);
        } catch (\Exception $e) {
            Log::error('Failed to create activity log: ' . $e->getMessage());
        }
    }

    /**
     * Log a system event
     */
    public function logSystemEvent(string $event, ?array $context = [], array $tags = ['system']): void
    {
        try {
            ActivityLog::log($event, null, null, $context, $tags);
            Log::info($event, $context);
        } catch (\Exception $e) {
            Log::error('Failed to log system event: ' . $e->getMessage());
        }
    }

    /**
     * Log a security event
     */
    public function logSecurityEvent(string $event, ?array $context = [], array $tags = ['security']): void
    {
        try {
            ActivityLog::log($event, null, null, $context, array_merge($tags, ['security']));
            Log::warning($event, $context);
        } catch (\Exception $e) {
            Log::error('Failed to log security event: ' . $e->getMessage());
        }
    }

    /**
     * Get activity logs with filters
     */
    public function getActivityLogs(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        \Log::info('Getting activity logs with filters:', ['filters' => $filters]);
        
        try {
            $query = ActivityLog::with('user')
                ->when(!empty($filters['user_id']), fn($q) => $q->where('user_id', $filters['user_id']))
                ->when(!empty($filters['event']), fn($q) => $q->where('event', 'like', "%{$filters['event']}%"))
                ->when(!empty($filters['type']), fn($q) => $q->where('auditable_type', $filters['type']))
                ->when(!empty($filters['tag']), fn($q) => $q->whereJsonContains('tags', $filters['tag']))
                ->when(!empty($filters['date_from']), fn($q) => $q->where('created_at', '>=', $filters['date_from']))
                ->when(!empty($filters['date_to']), fn($q) => $q->where('created_at', '<=', $filters['date_to']))
                ->latest();

            \Log::info('Query SQL:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching activity logs: ' . $e->getMessage());
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get security events with filters
     */
    public function getSecurityEvents(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $filters['tag'] = 'security';
        return $this->getActivityLogs($filters);
    }

    /**
     * Get system events with filters
     */
    public function getSystemEvents(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $filters['tag'] = 'system';
        return $this->getActivityLogs($filters);
    }

    /**
     * Get event statistics
     */
    public function getEventStatistics(): array
    {
        return [
            'total_events' => ActivityLog::count(),
            'user_actions' => ActivityLog::whereNotNull('user_id')->count(),
            'system_events' => ActivityLog::whereJsonContains('tags', 'system')->count(),
            'security_events' => ActivityLog::whereJsonContains('tags', 'security')->count(),
        ];
    }
}
