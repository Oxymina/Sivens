<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\User;
use App\Models\Comment;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'post_image',   
        'category_id',
        'author_id',
    ];
        public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function author() // Naming convention matches 'author_id'
    {
        return $this->belongsTo(User::class, 'author_id'); // Specify foreign key if not 'user_id'
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest(); // Order comments by newest first
    }

    public function likers() // Users who liked this post
    {
        return $this->belongsToMany(User::class, 'post_likes', 'post_id', 'user_id')->withTimestamps();
    }

    public function getLikesCountAttribute()
    {
        return $this->likers()->count();
    }

    public function getIsLikedAttribute()
    {
        if (!Auth::check()) {
            return false;
        }
        return $this->likers()->where('user_id', Auth::id())->exists();
    }

    protected $appends = [
        'is_liked',
    ];

}

