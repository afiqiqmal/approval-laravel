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
     * @param null $message1
     * @param null $message2
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectRouteBasedOnPermission($route1, $route2, $message1 = null, $message2 = null): \Illuminate\Http\RedirectResponse
    {
<<<<<<< HEAD
        if (!config('approval.enabled')) {
            return redirect()->route($route1)->withMessage($message1);
=======
        if (! config('approval.enabled')) {
            return redirect()->route($route1);
>>>>>>> adfce4245ff66b9c8101f0eb7b41ff4bec5e0eab
        }

        if ($this->hasFullApprovalPermission()) {
            return redirect()->route($route1)->withMessage($message1);
        }

        return redirect()->route($route2)->withMessage($message2);
    }
}
