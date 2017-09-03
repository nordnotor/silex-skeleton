<?php

namespace App\Models;

use App\Model;
use Carbon\Carbon;
use App\Traits\ModelAttachment;
use Illuminate\Validation\Rule;
use App\Interfaces\IdentityInterface;
use App\Providers\Rbac\Traits\IdentitySecurityTrait;

/**
 * Class User
 * @package App\Models
 *
 * @property string $_id
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property string $phone
 * @property string $sex
 * @property string $email
 * @property string $status
 * @property string $role
 *
 * @property string $password
 * @property string $old_password
 * @property string $password_confirmation
 *
 * @property string $password_hash
 * @property string $password_reset_token
 *
 * @property Carbon $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class User extends Model implements IdentityInterface
{
    use IdentitySecurityTrait, ModelAttachment;

    const SCENARIO_LOGIN = 'login';
    const SCENARIO_REGISTRATION = 'registration';
    const SCENARIO_PASSWORD_CHANGE = 'password_change';
    const SCENARIO_PASSWORD_RESET = 'password_reset';

    const STATUS_CREATED = 'created';
    const STATUS_BLOCKED = 'blocked';

    const SEX_MALE = 'male';
    const SEX_FEMALE = 'female';

    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at',
        'logged_at',
    ];

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'sex',
        'email',
        'phone',
        'role',
        'status',
        'password',
        'old_password',
        'password_confirmation',
        'logged_at', # on login this field update
    ];

    protected $appends = [
        'attachment',
    ];

    protected $hidden = [
        'password_hash',
        'password_reset_token',
    ];

    protected $forgotten = [
        'password_confirmation',
        'old_password',
        'code',
    ];

    protected $defaultValues = [
        'status' => self::STATUS_CREATED,
        'role' => [self::ROLE_USER],
    ];

    protected $scenarioRules = [

        self::SCENARIO_UPDATE => [
            'first_name' => 'required',
            'middle_name' => 'required',
            'last_name' => 'required',
            'sex' => 'required',
            'email' => 'required',
            'phone' => 'required',
        ],

        self::SCENARIO_PASSWORD_CHANGE => [
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ],
        self::SCENARIO_PASSWORD_RESET => [
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ],
        self::SCENARIO_REGISTRATION => [
            'first_name' => 'required',
            'last_name' => 'required',
            'sex' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ],
        self::SCENARIO_LOGIN => [
            'phone' => 'required',
            'password' => 'required|isValidPassword',
        ]
    ];

    public function rules(): array
    {
        return [
            'first_name' => 'string|max:255',
            'middle_name' => 'string|max:255',
            'last_name' => 'string|max:255',

            'email' => "email|unique:users,email,{$this->getKey()},_id",
            'phone' => "numeric|unique:users,phone,{$this->getKey()},_id",

            'password' => 'string',
            'old_password' => 'string',
            'password_confirmation' => 'string',

            'sex' => [Rule::in([self::SEX_MALE, self::SEX_FEMALE])],
            'role.*' => [Rule::in([self::ROLE_USER, self::ROLE_ADMIN])],
            'status' => [Rule::in([self::STATUS_BLOCKED, self::STATUS_CREATED])],
        ];
    }

    public function custom()
    {
        $this->addRule('isValidPassword', function ($attribute, $value, $parameters) {
            return $this->validatePassword($value);
        }, ':attribute is`t valid.');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {

            # if set password generate hash
            if ($password = $model->getAttribute('password')) {
                $model->setPassword($password);
                $model->forget('password');
            }

            if ($model->isScenario([self::SCENARIO_PASSWORD_RESET])) {
                $model->generatePasswordResetToken();
            }

            if ($model->isScenario([self::SCENARIO_REGISTRATION, self::SCENARIO_CREATE])) {
                $model->generatePasswordResetToken();
                $model->generateAuthKey();
            }

            return true;
        });

        static::saved(function (self $model) {

            $model->app('service.attachment')->upload($model->getKey(), $model->basename(), true);

            return true;
        });
    }

    # Identity Interface

    public function getRoles()
    {
        return $this->getAttribute('roles');
    }

    public function eraseCredentials()
    {
        # TODO: Implement eraseCredentials() method.
    }
}