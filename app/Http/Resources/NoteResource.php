<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'user_id' => $this->user_id,
            'private' => $this->private,
            'title' => $this->title,
            'text' => $this->text,
            'text_md' => $this->text_md,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_at_time_ago' => $this->created_at->diffForHumans(),
            'updated_at_time_ago' => $this->updated_at->diffForHumans(),
            'is_owner' => ($this->user_id == auth()->id()),
            'user' => $this->user,
            'has_attachments' => $this->hasAttachments(),
            'attachments' => $this->attachments,
            'has_sharing' => $this->hasShared(),
            'sharing' => $this->shared,
        ];
    }
}
