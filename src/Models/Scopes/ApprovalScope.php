<?php


namespace Afiqiqmal\Approval\Models\Scopes;


use Afiqiqmal\Approval\Approval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ApprovalScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected $extensions = ['IncludeNotApprove', 'OnlyNotApprove', 'WithApprovedOrNot'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (config('approval.enabled')) {
            $builder->where(function ($query) {
                return $query->with('approval')->doesntHave('approval')->orWhereHas('approval', function ($query) {
                    return $query->where('approved', true)->orWhere('status', 3);
                });
            });
        }
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

        if (config('approval.enabled') && config('approval.when.delete')) {
            $builder->onDelete(function (Builder $builder) {
                $models = $builder->get();

                foreach ($models as $model) {
                    $flag = false;

                    if (!$model->approval) {
                        Approval::updateApproval($model, 'delete');
                        $flag = true;
                    } else {
                        if (isset($model->approval->modification['mark']) && $model->approval->modification['mark'] != 'delete') {
                            Approval::updateApproval($model, 'delete');
                            $flag = true;
                        }
                    }

                    if (!$flag) {
                        $model->delete();
                    }
                }

                return true;
            });
        }
    }

    /**
     * Add the include Not Approve extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addIncludeNotApprove(Builder $builder)
    {
        $builder->macro('includeNotApprove', function (Builder $builder, bool $includeNotApprove = true) {
            if (!config('approval.enabled')) {
                return $builder->withoutGlobalScope($this);
            }

            if (!$includeNotApprove) {
                return $builder;
            }

            return $builder->withoutGlobalScope($this)->with('approval');
        });
    }

    /**
     * Add the Only Not Approve extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addOnlyNotApprove(Builder $builder)
    {
        $builder->macro('onlyNotApprove', function (Builder $builder) {
            if (!config('approval.enabled')) {
                return $builder->withoutGlobalScope($this);
            }

            $builder->withoutGlobalScope($this)->whereHas('approval', function ($query) {
                return $query->where('approved', false)->where('status', 1);
            })->with('approval');

            return $builder;
        });
    }

    /**
     * Add the include Not Approve extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addWithApprovedOrNot(Builder $builder)
    {
        $builder->macro('withApprovedOrNot', function (Builder $builder, $approvedOnly = true) {
            if (!config('approval.enabled')) {
                return $builder->withoutGlobalScope($this);
            }

            if (is_null($approvedOnly) || $approvedOnly) {
                return $builder;
            }

            return $builder->onlyNotApprove();
        });
    }
}
