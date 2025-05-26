<?php

namespace App\Traits;

use App\Services\AuditLogService;

trait Auditable
{
    /**
     * Boot the trait
     */
    protected static function bootAuditable(): void
    {
        // Log model creation
        static::created(function ($model) {
            app(AuditLogService::class)->logUserAction(
                'created',
                $model,
                null,
                $model->getAttributes(),
                ['model', 'create']
            );
        });

        // Log model updates
        static::updated(function ($model) {
            $changes = $model->getDirty();
            $original = array_intersect_key($model->getOriginal(), $changes);

            app(AuditLogService::class)->logUserAction(
                'updated',
                $model,
                $original,
                $changes,
                ['model', 'update']
            );
        });

        // Log model deletion
        static::deleted(function ($model) {
            app(AuditLogService::class)->logUserAction(
                'deleted',
                $model,
                $model->getAttributes(),
                null,
                ['model', 'delete']
            );
        });

        // Log model restoration (if using soft deletes)
        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                app(AuditLogService::class)->logUserAction(
                    'restored',
                    $model,
                    null,
                    $model->getAttributes(),
                    ['model', 'restore']
                );
            });
        }
    }
}
