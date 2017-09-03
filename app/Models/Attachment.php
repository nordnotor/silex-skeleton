<?php

namespace App\Models;

use App\Model;
use Carbon\Carbon;
use League\Flysystem\File;
use League\Flysystem\Filesystem;

/**
 * Class Attachment
 * @package App\Models
 *
 * Base uploader class use with App\Traits\attachment and flysystem
 *
 * @property string $_id
 *
 * @property string $name
 * @property string $obj_id
 * @property string $obj_type
 * @property string $path
 *
 * @property array $file
 * @property string $ext
 * @property string $real_name
 * @property string $mime_type
 * @property string $size
 *
 * @property string $fly_system
 *
 * @property Filesystem $system
 * @property File $file_filesystem
 *
 * @property string $url
 *
 * @property Carbon $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Attachment extends Model
{
    protected $fillable = [
        'obj_id',
        'obj_type',

        'file',
        'path',
        'fly_system',
    ];

    protected $appends = [
        'url'
    ];

    protected $hidden = [
        'created_at',
        'created_by',
        'updated_at',

        'path',
        'obj_id',
        'obj_type',
        'file_system',
    ];

    public static function boot()
    {
        parent::boot();

        self::deleting(function (self $model) {

            $model->app('service.attachment')->deleteFile($model->path, $model->fly_system);

            return true;
        });
    }

    # getter

    /**
     * @return string
     */
    public function getUrlAttribute()
    {
        return $this->app('service.attachment')->getUrl($this);
    }
}
