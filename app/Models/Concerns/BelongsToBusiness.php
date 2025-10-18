<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToBusiness
{
    public static function bootBelongsToBusiness(): void
    {
        // create ke waqt business_id auto set
        static::creating(function ($model) {
            if (blank($model->business_id) && auth()->check()) {
                $bid = auth()->user()->current_business_id ?? session('active_business_id');
                if ($bid) {
                    $model->business_id = $bid;
                }
            }
        });

        // har query ko active business tak restrict
        static::addGlobalScope('business', function (Builder $builder) {
            if (auth()->check()) {
                $bid = auth()->user()->current_business_id ?? session('active_business_id');
                if ($bid) {
                    $builder->where($builder->getModel()->getTable().'.business_id', $bid);
                }
            }
        });
    }

    public function business()
    {
        return $this->belongsTo(\App\Models\Business::class);
    }
}
