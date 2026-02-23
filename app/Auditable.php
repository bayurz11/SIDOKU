<?php

namespace App;

use App\Models\LogChange;
use Illuminate\Support\Facades\Log;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            self::storeLog($model, 'created');
        });

        static::updated(function ($model) {
            self::storeLog($model, 'updated');
        });

        static::deleted(function ($model) {
            self::storeLog($model, 'deleted');
        });
    }

    protected static function storeLog($model, $action)
    {
        $log = Log::create([
            'model_type' => get_class($model),
            'model_id'   => $model->id,
            'action'     => $action,
            'user_id'    => auth()->id(),
            'ip_address' => request()->ip(),
        ]);

        if ($action === 'updated') {

            foreach ($model->getDirty() as $field => $newValue) {

                $oldValue = $model->getOriginal($field);

                if ($oldValue != $newValue) {

                    LogChange::create([
                        'log_id'    => $log->id,
                        'field'     => $field,
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                    ]);
                }
            }
        }
    }
}
