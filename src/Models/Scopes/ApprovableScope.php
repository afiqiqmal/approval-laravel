<?php


namespace Afiqiqmal\Approval\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ApprovableScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected $extensions = ['Approve', 'Reject'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {

    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Add the reject extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addReject(Builder $builder)
    {
        $builder->macro('reject', function (Builder $builder, $reason = null) {

            if (!config('approval.enabled')) {
                return $builder->withoutGlobalScope($this);
            }

            foreach ($builder->get() as $item) {
                $item->reject($reason);
            }

            return true;
        });
    }

    /**
     * Add the approve extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addApprove(Builder $builder)
    {
        $builder->macro('approve', function (Builder $builder, $reason = null) {
            if (!config('approval.enabled')) {
                return $builder->withoutGlobalScope($this);
            }

            foreach ($builder->get() as $item) {
                $item->approve($reason);
            }

            return true;
        });
    }
}
