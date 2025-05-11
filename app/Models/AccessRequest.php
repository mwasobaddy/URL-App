<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessRequest extends Model
{
    protected $fillable = ['url_list_id', 'requester_id', 'message', 'status'];

    /**
     * Get the URL list that this access request is for
     */
    public function urlList(): BelongsTo
    {
        return $this->belongsTo(UrlList::class);
    }

    /**
     * Get the user who requested access
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}
