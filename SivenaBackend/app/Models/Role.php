<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Define common role names as constants for easy reference
    public const ROLE_READER = 'reader';
    public const ROLE_WRITER = 'writer';
    public const ROLE_ADMIN  = 'admin';

    public function users()
    {
        return $this->hasMany(User::class);
    }
}