<?php

namespace Afiqiqmal\Approval\Observers;

use Illuminate\Database\Eloquent\Model;

class ApprovableObserver
{
    public function updated(Model $model)
    {
        if (method_exists($model->approvable, 'flushCache')) {
            $model->approvable->flushCache();
        }
    }
}
