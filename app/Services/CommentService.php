<?php

namespace App\Services;

use App\Model;
use App\Models\Attachment;
use App\Models\Comment;
use Illuminate\Support\Collection;
use League\Flysystem\File;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;

/**
 * Class AttachmentService
 * @package App\Services
 */
class CommentService extends CoreService
{
    public function refreshUserMark(Comment $model)
    {
        if ($model->type === Comment::TYPE_REVIEW && in_array($model->obj_type, ['user', 'clinic'])) {

            $result = $this->app->model('comment')->raw(function ($collection) use ($model) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'type' => ['$in' => [Comment::TYPE_REVIEW]],
                            'obj_type' => $model->obj_type,
                            'obj_id' => $model->obj_id,
                            'status' => Comment::STATUS_VERIFIED,
                            'deleted_at' => ['$exists' => false],
                            'parent_id' => ['$exists' => false],
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => null,
                            'count' => ['$sum' => 1],
                            'total' => ['$sum' => '$mark'],
                        ]
                    ]
                ]);
            });

            $total = $result[0]['total'] ?? 0;
            $count = $result[0]['count'] ?? 0;

            return $this->app->model($model->obj_type)->where('_id', '=', $model->obj_id)->update([
                'mark' => $count ? $total / $count : $count,
                'mark_count' => $count,
            ]);
        }

    }


    public function urlObject(Comment $model)
    {
        $obj_type = $model->getAttribute('obj_type');
        $obj_id = $model->getAttribute('obj_id');

        if (isset($obj_type, $obj_id) && in_array($obj_type, Comment::OBJECT_URL_ENABLE)) {
            return $this->app->url("$obj_type.view", ['id' => $obj_id]);
        }
        return '';
    }

    public function urlParent(Comment $model)
    {
        if (null !== $parentId = $model->getAttribute('parent_id')) {
            return $this->app->url('comment.view', ['id' => $parentId]);
        }
        return '';
    }
}