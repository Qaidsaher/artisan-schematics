<?php
namespace Saher\ArtisanSchematics\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Saher\ArtisanSchematics\Tests\Fixtures\Enums\PostStatus;

class Post extends Model
{
    protected $casts = [
        'is_published' => 'boolean',
        'status' => PostStatus::class,
        'tags' => 'array',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}