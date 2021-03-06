<?php

namespace Afiqiqmal\Approval\Models;

use Afiqiqmal\Approval\Models\Scopes\ApprovableScope;
use Afiqiqmal\Approval\Observers\ApprovableObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Config;
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
        'mark',
        'approved_by',
        'rejected_by',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'modification' => 'json',
    ];

    protected $appends = [
        'approvable_type_formatted',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(app(ApprovableScope::class));
        static::observe(app(ApprovableObserver::class));
    }

    private static function getMarkRemark($mark)
    {
        if ($mark == 'create') {
            return "New Data";
        }
        if ($mark == 'update') {
            return "Request for Information Update";
        }
        if ($mark == 'delete') {
            return "Request for Deletion";
        }

        return null;
    }

    public function approvable()
    {
        return $this->morphTo(__FUNCTION__, 'approvable_type', 'approvable_id')->withoutGlobalScopes()->withTrashed();
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

    public function scopeFilter($query, array $through = [])
    {
        return app(Pipeline::class)
            ->send($query)
            ->through($through)
            ->thenReturn();
    }

    public function getApprovableTypeFormattedAttribute()
    {
        return trim(ucwords(implode(' ', preg_split('/(?=[A-Z])/', class_basename($this->attributes['approvable_type'])))));
    }

    public function approve($reason = null)
    {
        if ($this->mark == 'delete') {
            $this->approvable()->delete();
        } else {
            $this->update([
                'remarks' => $reason,
                'approved' => true,
                'status' => 2,
                'approved_by' => auth('cms')->id(),
                'approved_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
                'mark' => 'approved',
            ]);
        }

        self::triggerEvent('approved', $this);
    }

    public function reject($reason = null)
    {
        $this->update([
            'remarks' => $reason,
            'approved' => false,
            'status' => 3,
            'rejected_by' => auth('cms')->id(),
            'rejected_at' => now(),
            'approved_by' => null,
            'approved_at' => null,
            'mark' => 'rejected',
        ]);

        self::triggerEvent('rejected', $this);
    }

    public function getActionUrl($action = null)
    {
        return URL::temporarySignedRoute('approval.link', now()->addHours(2), [
            'approval' => encrypt($this->hashslug),
            'action' => $action ? encrypt($action) : null,
        ]);
    }

    public static function createApproval($model, $mark = 'create')
    {
        $needApproval = auth()->user() && method_exists(auth()->user(), 'canMakeApprovalOrReject') &&
            ! auth()->user()->canMakeApprovalOrReject();

        if ($needApproval) {
            $model->approval()->create([
                'hashslug' => Str::random(60),
                'approved' => false,
                'status' => 1,
                'mark' => $mark,
                'remarks' => self::getMarkRemark($mark),
                'modification' => json_encode($model->getDirty()),
            ]);

            self::triggerEvent('requested', $model->approval);
        } else {
            $approval = $model->approval()->create([
                'hashslug' => Str::random(60),
                'approved' => true,
                'status' => 2,
                'mark' => $mark,
                'remarks' => self::getMarkRemark($mark),
                'modification' => json_encode($model->getDirty()),
            ]);

            self::triggerEvent('approved', $approval);
        }

        if (method_exists($model,  'flushCache')) {
            $model->flushCache();
        }
    }

    public static function updateApproval($model, $mark = 'update')
    {
        $needApproval = auth()->user() && method_exists(auth()->user(), 'canMakeApprovalOrReject') &&
            ! auth()->user()->canMakeApprovalOrReject();

        if (! $model->approval) {
            self::createApproval($model, $mark);
        } else {
            if ($needApproval) {
                $model->approval()->update([
                    'approved' => false,
                    'status' => 1,
                    'mark' => $mark,
                    'remarks' => self::getMarkRemark($mark),
                    'modification' => json_encode($model->getDirty()),
                ]);

                self::triggerEvent('requested', $model->approval);
            } else {
                $model->approval()->update([
                    'approved' => true,
                    'status' => 2,
                    'mark' => $mark,
                    'remarks' => self::getMarkRemark($mark),
                    'modification' => json_encode($model->getDirty()),
                ]);

                self::triggerEvent('approved', $model->approval);
            }
        }

        if (method_exists($model,  'flushCache')) {
            $model->flushCache();
        }
    }

    private static function triggerEvent($type, $model)
    {
        $event = Config::get("approval.events.$type");
        event(new $event($model));
    }
}
