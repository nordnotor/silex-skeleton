<?php

namespace App;

use App\Traits\ModelHelpers;
use App\Traits\ModelValidate;
use App\Traits\ModelRestriction;
use App\Interfaces\ModelInterface;
use Illuminate\Database\Eloquent\Scope;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Model extends Eloquent implements Scope, ModelInterface
{
    use ModelValidate, ModelHelpers, ModelRestriction, SoftDeletes;

    const SCENARIO_UPDATE = 'update';
    const SCENARIO_CREATE = 'create';
    const SCENARIO_LIST = 'list';

    protected $guarded = [];

    protected $forgotten = [];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected function getDateFormat()
    {
        return $this->dateFormat ?? 'U'; # 'Y-m-d H:i:s';
    }

    protected static function boot()
    {
        parent::boot();
    }

    public function save(array $options = [])
    {
        $user = $this->getAuthUser();

        if ($this->exists) {
            $this->setAttribute($this->updatedByColumn(), $user ? $user->getKey() : null);
        } else {
            $this->setAttribute($this->createdByColumn(), $user ? $user->getKey() : null);
        }

        # forget fields
        foreach ($this->forgotten as $forgotten) {
            $this->forget($forgotten);
        }

        return parent::save($options);
    }
}
