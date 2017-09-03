<?php

namespace App\Services;

use App\Model;
use App\Models\Attachment;
use Illuminate\Support\Collection;
use League\Flysystem\File;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;

/**
 * Class AttachmentService
 * @package App\Services
 */
class AttachmentService extends CoreService
{
    const DEFAULT_FLY_SYSTEM = 'local';

    /**
     * Upload all files.
     *
     * @param string $obj_type
     * @param string $obj_id
     * @param bool $remove
     * @return Collection
     */
    public function upload(string $obj_id, string $obj_type, bool $remove = false)
    {
        /** @var FileBag $files @var UploadedFile $file */

        $collection = new Collection();
        $files = $this->app->request()->files;

        foreach ($files as $file) {
            if (!isset($file) || !$file->isValid()) {
                return $collection;
            }
        }

        if ($files->count() && $remove) {
            $this->delete($obj_id, $obj_type);
        }

        foreach ($files as $file) {
            if ($attachment = $this->uploadFile($file, $obj_id, $obj_type)) {
                $collection->push($attachment);
            }
        }
        return $collection;
    }

    /**
     * Upload one attachment.
     *
     * @param UploadedFile $file
     * @param string $obj_id
     * @param string $obj_type
     * @param string $fly_system
     * @return Attachment|bool
     */
    public function uploadFile(UploadedFile $file, string $obj_id, string $obj_type, string $fly_system = self::DEFAULT_FLY_SYSTEM)
    {
        /** @var \App\Models\Attachment $attachment */
        $attachment = $this->app->model('attachment');

        $attachment->setAttribute('obj_id', $obj_id);
        $attachment->setAttribute('obj_type', $obj_type);
        $attachment->setAttribute('fly_system', $fly_system);

        $filename = uniqid("$obj_id|", false);

        $attachment->fill([
            'path' => "$filename.{$file->getClientOriginalExtension()}",
            'file' => [
                'ext' => $file->getClientOriginalExtension(),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]
        ]);


        if (!$file->isValid()) {
            return false;
        }

        $stream = fopen($file->getRealPath(), 'rb+');
        $this->getFlySystem($attachment->fly_system)->writeStream($attachment->path, $stream);

        if (fclose($stream) && $attachment->save()) {
            return $attachment;
        }
        return false;
    }

    /**
     * Delete attachments.
     *
     * @param string $obj_id
     * @param string $obj_type
     * @return void
     *
     * @internal param UploadedFile $file
     * @internal param Attachment $attachment
     */
    public function delete(string $obj_id, string $obj_type)
    {
        /** @var Attachment[] $models */
        $models = $this->app->model('attachment')->where('obj_id', $obj_id)->where('obj_type', $obj_type)->get();

        foreach ($models as $model) {

            if ($model->delete()) {
                $this->deleteFile($model->path, $model->fly_system);
            }
        }
    }

    /**
     * @param string $path
     * @param string|Filesystem $flySystem
     * @return bool
     */
    public function deleteFile(string $path, $flySystem): bool
    {
        $filesystem = is_string($flySystem) ? $this->getFlySystem($flySystem) : $flySystem;

        if ($filesystem && $filesystem->has($path)) {
            return $filesystem->delete($path);
        }
        return false;
    }

    /**
     * Download file.
     *
     * @param Attachment $attachment
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Attachment $attachment)
    {
        $headers = [];
        $filesystem = $this->getFlySystem($attachment->fly_system);
        $file = new File($filesystem, $attachment->path);
        $name = $attachment->getAttribute('file.original_name', '');

        $stream = function () use ($file, $filesystem) {
            readfile($filesystem->getAdapter()->applyPathPrefix($file->getPath()));
        };

        $baseHeaders = [
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
            'Content-Type' => $file->getMimetype(),
            'Content-Length' => $file->getSize(),
        ];

        foreach ($headers as $key => $value) {
            $baseHeaders[$key] = $value;
        }
        return $this->app->stream($stream, 200, $baseHeaders);
    }

    public function getUrl(Attachment $attachment)
    {
        if (null !== $filesystem = $this->getFlySystem($attachment->fly_system)) {
            $basePath = $filesystem->getConfig()->get('asset.path');
            return $this->app->request()->getSchemeAndHttpHost() . $basePath . $attachment->path;
        }
        return null;
    }

    /**
     * @param string $name
     * @return Filesystem
     */
    public function getFlySystem(string $name = self::DEFAULT_FLY_SYSTEM)
    {
        return $this->app['flysystem']($name);
    }

}