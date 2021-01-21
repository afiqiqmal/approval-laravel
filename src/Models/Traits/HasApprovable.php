<?php


namespace Afiqiqmal\Approval\Models\Traits;

trait HasApprovable
{
    public static function bootHasApprovable()
    {
    }

    /**
     * @return bool
     */
    public function canMakeApproval(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canMakeReject(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function hasFullApprovalPermission(): bool
    {
        return $this->canMakeApproval() && $this->canMakeReject();
    }

    /**
     * @return bool
     */
    public function canMakeApprovalOrReject(): bool
    {
        return$this->canMakeApproval() || $this->canMakeReject();
    }

    /**
     * @param $route1
     * @param $route2
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectRouteBasedOnPermission($route1, $route2): \Illuminate\Http\RedirectResponse
    {
        if (! config('approval.enabled')) {
            return redirect()->route($route1);
        }

        if ($this->hasFullApprovalPermission()) {
            return redirect()->route($route1);
        }

        return redirect()->route($route2);
    }
}
