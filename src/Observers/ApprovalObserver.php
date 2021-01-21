<?php

namespace Afiqiqmal\Approval\Observers;

use Afiqiqmal\Approval\Events\ApprovalRequested;
use Afiqiqmal\Approval\Models\Approval;
use Illuminate\Database\Eloquent\Model;

class ApprovalObserver
{
    public function created(Model $model)
    {
        if (config('approval.enabled') && config('approval.when.create')) {
            Approval::createApproval($model);
        }
    }

    public function updated(Model $model)
    {
        if (config('approval.enabled') && config('approval.when.update')) {
            Approval::updateApproval($model);
        }
    }
}
