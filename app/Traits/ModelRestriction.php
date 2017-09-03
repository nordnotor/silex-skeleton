<?php

namespace App\Traits;

use App\Interfaces\IdentityInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

trait ModelRestriction
{
    public static function bootModelRestriction()
    {
        static::addGlobalScope(new self);
    }

    public function apply(Builder $builder, Model $model)
    {
        $user = $this->getAuthUser();

        if (isset($user) && in_array($user->getRoles(),  $this->getModelRestrictionRole())) {
            $builder->where([$this->createdByColumn() => $user->getKey()]);
        }
    }

    # Help functions

    /**
     * @return IdentityInterface|null|Model
     */
    protected function getAuthUser()
    {
        if (null !== $user = $this->app('security.user')) {
            return $user;
        }
        return null;
    }

    # Getters

    public function getModelRestrictionRole(): array
    {
        return $this->restriction ?? [];
    }

    public function createdByColumn(): string
    {
        return defined('static::CREATED_BY') ? static::CREATED_BY : 'created_by';
    }

    public function updatedByColumn(): string
    {
        return defined('static::CREATED_BY') ? static::UPDATED_BY : 'updated_by';
    }

# Readers

#    public function getCreatedByAttribute()
#    {
#        return $this->app()->model('user')->where('_id', '=', array_get($this->attributes, $this->createdByColumn(), ''))->first(['_id', 'first_name', 'middle_name', 'last_name']);
#    }
#
#    public function getUpdatedByAttribute()
#    {
#        return $this->app()->model('user')->where('_id', '=', array_get($this->attributes, $this->updatedByColumn(), ''))->first(['_id', 'first_name', 'middle_name', 'last_name']);
#    }
}