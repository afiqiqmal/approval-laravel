<?php

namespace Afiqiqmal\Approval\Models;

use Afiqiqmal\Approval\Events\ApprovalRequested;
use Afiqiqmal\Approval\Events\Approved;
use Afiqiqmal\Approval\Events\Rejected;
use Afiqiqmal\Approval\Models\Scopes\ApprovableScope;
use Afiqiqmal\Approval\Observers\ApprovableObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'hashslug',
        'approvable_id',
        'approvable_type',
        'approved',
        'remarks',
        'status',
        'modification',
        'approved_by',
        'rejected_by',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'modification' => 'json',
    ];

    protected $hidden = [
        'approvable_type',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(app(ApprovableScope::class));
        static::observe(app(ApprovableObserver::class));
    }

    public function approvable()
    {
        return $this->morphTo(__FUNCTION__, 'approvable_type', 'approvable_id')->includeNotApprove()->withTrashed();
    }

    public function rejectedBy()
    {
        return $this->belongsTo(config('approval.model.reject_by'), 'rejected_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(config('approval.model.approve_by'), 'approved_by');
    }

    public function scopeRequester($query, $id = null)
    {
        return $query->where('created_by', $id ?? auth()->id());
    }

    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    public function scopeNotApproved($query)
    {
        return $query->where('approved', false);
    }

    public function scopePending($query)
    {
        return $query->where('status', 1);
    }

    public function approve($reason = null)
    {
        if ($this->modification['mark'] == 'delete') {
            $this->approvable()->delete();
        } else {
            $this->update(self::approvedArray($reason));
        }

        event(new Approved($this));
    }

    public function reject($reason = null)
    {
        $this->update(self::rejectArray($reason));
        event(new Rejected($this));
    }

    public function getActionUrl($action = null)
    {
        return URL::temporarySignedRoute('approval.link', now()->addHours(2), [
            'approval' => encrypt($this->hashslug),
            'action' => $action ? encrypt($action) : null,
        ]);
    }

    public static function approvedArray($reason = null)
    {
        return [
            'remarks' => $reason,
            'approved' => true,
            'status' => 2,
            'approved_by' => auth('cms')->id(),
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'modification' => [
                'mark' => 'approved',
            ],
        ];
    }

    public static function rejectArray($reason = null)
    {
        return [
            'remarks' => $reason,
            'approved' => false,
            'status' => 3,
            'rejected_by' => auth('cms')->id(),
            'rejected_at' => now(),
            'approved_by' => null,
            'approved_at' => null,
            'modification' => [
                'mark' => 'rejected',
            ],
        ];
    }

    public static function createApproval($model, $modification = 'create')
    {
        $needApproval = method_exists(auth()->user(), 'hasFullApprovalPermission') &&
            ! auth()->user()->hasFullApprovalPermission();

        if ($needApproval) {
            $model->approval()->create([
                'hashslug' => Str::random(60),
                'approved' => false,
                'status' => 1,
                'modification' => [
                    'mark' => $modification,
                ],
            ]);

            self::afterEvent($model);
        } else {
            $model->approval()->create([
                'hashslug' => Str::random(60),
                'approved' => true,
                'status' => 2,
                'modification' => [
                    'mark' => $modification,
                ],
            ]);
        }

        if (method_exists($model,  'flushCache')) {
            $model->flushCache();
        }
    }

    public static function updateApproval($model, $modification = 'update')
    {
        $needApproval = method_exists(auth()->user(), 'hasFullApprovalPermission') &&
            ! auth()->user()->hasFullApprovalPermission();

        if (! $model->approval) {
            self::createApproval($model, $modification);
        } else {
            if ($needApproval) {
                $model->approval()->update([
                    'approved' => false,
                    'status' => 1,
                    'modification' => [
                        'mark' => $modification,
                    ],
                    'remarks' => $modification == 'delete' ? 'Request for Deletion' : null,
                ]);

                self::afterEvent($model);
            } else {
                $model->approval()->update([
                    'approved' => true,
                    'status' => 2,
                    'modification' => [
                        'mark' => $modification,
                    ],
                    'remarks' => $modification == 'delete' ? 'Request for Deletion' : null,
                ]);
            }
        }

        if (method_exists($model,  'flushCache')) {
            $model->flushCache();
        }
    }

    public static function afterEvent($model)
    {
        if ($model->enableNotification()) {
            event(new ApprovalRequested($model));
        }
    }
}
