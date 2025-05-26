<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuditLogService;

class LogUserActions
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Skip logging for certain paths
        if ($this->shouldSkipLogging($request)) {
            return $response;
        }

        // Log the action
        $this->auditLogService->logUserAction(
            $this->getEventName($request),
            null,
            null,
            $this->getRequestData($request),
            ['http']
        );

        return $response;
    }

    protected function shouldSkipLogging(Request $request): bool
    {
        return $request->is(
            'livewire/message/*',
            'livewire/upload-file',
            '_debugbar/*',
            'sanctum/*',
            'log-viewer/*'
        ) || $request->ajax();
    }

    protected function getEventName(Request $request): string
    {
        return sprintf(
            '%s %s',
            strtoupper($request->method()),
            $request->route()?->getName() ?? $request->path()
        );
    }

    protected function getRequestData(Request $request): array
    {
        return [
            'method' => $request->method(),
            'path' => $request->path(),
            'route' => $request->route()?->getName(),
            'parameters' => $this->sanitizeParameters($request->route()?->parameters() ?? []),
        ];
    }

    protected function sanitizeParameters(array $parameters): array
    {
        // Remove sensitive data
        unset($parameters['password'], $parameters['token']);
        return $parameters;
    }
}
