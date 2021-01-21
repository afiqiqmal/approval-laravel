<?php


namespace Afiqiqmal\Approval\Models\Traits;

use Afiqiqmal\Approval\Models\Approval;
use Afiqiqmal\Approval\Models\ApprovalModelContent;
use Afiqiqmal\Approval\Models\Scopes\ApprovalScope;
use Afiqiqmal\Approval\Observers\ApprovalObserver;

trait RequireApproval
{
    public static function bootRequireApproval()
    {
        static::observe(app(ApprovalObserver::class));
        static::addGlobalScope(app(ApprovalScope::class));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function approval(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Approval::class, 'approvable');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopePendingApproval($query)
    {
        return $query->whereHas('approval', function ($query) {
            $query->pending();
        });
    }

    /**
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete()
    {
        if (config('approval.enabled') && config('approval.when.delete')) {
            if (! $this->approval) {
                Approval::updateApproval($this, 'delete');

                return true;
            } else {
                if (isset($this->approval->modification['mark']) && $this->approval->modification['mark'] != 'delete') {
                    Approval::updateApproval($this, 'delete');

                    return true;
                }
            }
        }

        return parent::delete();
    }

    /**
     * Custom action after approved
     *
     * @return bool
     */
    public function actionAfterApproved()
    {
        return true;
    }

    /**
     * Custom action after rejected
     *
     * @return bool
     */
    public function actionAfterReject()
    {
        return true;
    }

    /**
     * Custom action before rejected
     *
     * @return bool
     */
    public function actionBeforeApproved()
    {
        return true;
    }

    /**
     * Custom action before rejected
     *
     * @return bool
     */
    public function actionBeforeReject()
    {
        return true;
    }

    /**
     * Get list of alert permissions
     *
     * @return string[]
     */
    public function assignedAlertPermission(): array
    {
        return [];
    }

    /**
     * Get list of gate permission
     *
     * @return string[]
     */
    public function assignedGatePermission(): array
    {
        return [];
    }

    /**
     * Control notification
     *
     * @return bool
     */
    public function enableNotification(): bool
    {
        return true;
    }

    /**
     * Use for naming tagging
     *
     * @return string
     */
    public function approvalModule(): string
    {
        return trim(ucwords(implode(' ', preg_split('/(?=[A-Z])/', class_basename($this)))));
    }

    /**
     * See who allowed to receive the email to approve
     *
     * @param null $user
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function whoAllowedToReceiveNotification($user = null)
    {
        return [];
    }

    /**
     * Used for email detail
     *
     * @return ApprovalModelContent|null
     */
    public function getCommonContents() : ?ApprovalModelContent
    {
        return null;
    }

    /**
     * Correct the route binding for approval with global scope
     *
     * @param $value
     * @param null $field
     * @return mixed
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->includeNotApprove()->where($field ?? $this->getRouteKeyName(), $value)->first();
    }
}
