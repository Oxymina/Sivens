<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Role;


class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'userPFP',
        'role_id',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $with = ['role'];

    protected $appends = ['role_name'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    // Comments made by this user
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    // Posts liked by this user
    public function likedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_likes', 'user_id', 'post_id')->withTimestamps();
    }
    // User role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    // Role name
    public function getRoleNameAttribute()
    {
        return $this->role ? $this->role->name : null;
    }
    // Check if user relationship role exists and its name matches
    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }
    // Check what type of role user has
    public function isAdmin()
    {
        return $this->hasRole(Role::ROLE_ADMIN);
    }
    public function isWriter()
    {
        return $this->hasRole(Role::ROLE_WRITER);
    }

    public function isReader()
    {
        return $this->hasRole(Role::ROLE_READER);
    }

}
