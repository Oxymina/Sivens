<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;

class AdminRoleController extends Controller
{
    public function index()
    {
        Gate::authorize('manage-users'); // Assuming roles are tied to user management permission
        return response()->json(Role::orderBy('name')->get(['id', 'name']));
    }
}