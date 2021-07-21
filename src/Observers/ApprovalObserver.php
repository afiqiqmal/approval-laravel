<?php

namespace Afiqiqmal\Approval\Observers;

use Illuminate\Database\Eloquent\Model;

class ApprovalObserver
{
    public function created(Model $model)
    {
        if (config('approval.enabled') && config('approval.when.create')) {
            app()->make(config('approval.model.approval'))::createApproval($model);
        }
    }

    public function updated(Model $model)
    {
        if (config('approval.enabled') && config('approval.when.update')) {
            app()->make(config('approval.model.approval'))::updateApproval($model);
        }
    }
}
