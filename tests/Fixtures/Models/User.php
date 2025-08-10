<?php
namespace Saher\ArtisanSchematics\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class User extends Model {
	public function posts(): HasMany
	{
		return $this->hasMany(Post::class);
	}

	public function tags(): BelongsToMany
	{
		return $this->belongsToMany(Tag::class);
	}

	public function comments(): MorphMany
	{
		return $this->morphMany(Comment::class, 'commentable');
	}

	public function country(): HasOneThrough
	{
		return $this->hasOneThrough(Country::class, Address::class);
	}
}