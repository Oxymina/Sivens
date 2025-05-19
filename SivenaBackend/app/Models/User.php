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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'userPFP',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
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

    public function likedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_likes', 'user_id', 'post_id')->withTimestamps();
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    public function getRoleNameAttribute()
    {
        return $this->role ? $this->role->name : null;
    }
    public function hasRole($roleName)
    {
        // Check if user relationship role exists and its name matches
        return $this->role && $this->role->name === $roleName;
    }
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
        return $this->hasRole(Role::ROLE_READER); // Or simply !isAdmin() && !isWriter() if default
    }

}
