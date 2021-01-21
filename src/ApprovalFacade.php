<?php

namespace Afiqiqmal\Approval;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Afiqiqmal\Approval\Approval
 */
class ApprovalFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'approval';
    }
}
