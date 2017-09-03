<?php

namespace App\Models;

use App\Interfaces\IdentityInterface;
use App\Model;

/**
 * Class Login
 * @package App\Models
 *
 * @property string $email
 * @property string $password
 * @property string $remember
 */
class Login extends Model
{
    private $_user = false;

    protected $fillable = [
        'email',
        'password',
        'remember',
    ];

    public function rules(): array
    {
        return [
            'email' => 'required',
            'password' => 'required',
            'remember' => 'bool',
        ];
    }

    public function login()
    {
        return $this->validate() && $this->getUserAttribute();
    }

    /**
     * @return IdentityInterface|null
     */
    public function getUserAttribute()
    {
        $app = $this->app();
        $model = $app->model('user');

        if (!$this->_user && $u = $model->where('email', '=', $this->email)->first()) {
            $this->setUserAttribute($u);
        }
        return $this->attributes['_user'] ?? null;
    }

    public function setUserAttribute(IdentityInterface $user)
    {
        $this->attributes['_user'] = $user;
    }
}