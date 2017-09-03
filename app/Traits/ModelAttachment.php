<?php

namespace App\Traits;

/**
 * @property \App\Models\Attachment|null $attachment
 * @property \Illuminate\Support\Collection $attachments
 */
trait ModelAttachment
{
    /**
     * Base model query to attachments.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function attachmentsQuery()
    {
        return $this->app()->model('attachment')
            ->where('obj_id', '=', $this->getKey())
            ->where('obj_type', '=', $this->basename());
    }

    # Getters

    /**
     * Get first attachment.
     *
     * @return \App\Models\Attachment|array|null
     */
    public function getAttachmentAttribute()
    {
        return $this->attachmentsQuery()->first();
    }

    /**
     * Get all attachments.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAttachmentsAttribute()
    {
        return $this->attachmentsQuery()->get();
    }
}