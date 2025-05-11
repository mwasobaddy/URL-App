<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Url extends Model
{
    protected $fillable = ['url_list_id', 'url', 'title', 'description'];

    public function urlList(): BelongsTo
    {
        return $this->belongsTo(UrlList::class);
    }
}
