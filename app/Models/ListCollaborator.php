<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListCollaborator extends Model
{
    protected $fillable = ['url_list_id', 'user_id'];

    /**
     * Get the URL list that this collaboration is for
     */
    public function urlList(): BelongsTo
    {
        return $this->belongsTo(UrlList::class);
    }

    /**
     * Get the user who is a collaborator
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
