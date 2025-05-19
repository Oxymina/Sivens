<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // Use your main base controller
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller // Or extends Admin\AdminController
{
    public function index(Request $request)
    {
        Gate::authorize('manage-users');

        $query = User::with('role');

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm);
            });
        }

        $sortBy = $request->input('sortBy', 'created_at');
        $sortDesc = $request->input('sortDesc', 'true') === 'true' ? 'desc' : 'asc';
        // Validate sortBy against allowed columns to prevent SQL injection
        $allowedSorts = ['name', 'email', 'created_at', 'role_id']; // Role sorting needs more work via join
        if (in_array($sortBy, $allowedSorts)) {
             if ($sortBy === 'role_id') {
                // More complex sorting by role name:
                // $query->join('roles', 'users.role_id', '=', 'roles.id')->orderBy('roles.name', $sortDesc);
                // For simplicity, we'll sort by role_id now. Frontend can display role.name.
                 $query->orderBy('role_id', $sortDesc);
             } else {
                 $query->orderBy($sortBy, $sortDesc);
             }
        } else {
             $query->orderBy('created_at', 'desc'); // Default sort
        }


        $users = $query->paginate($request->input('perPage', 10));
        return response()->json($users);
    }

    /**
     * For dropdowns, etc. Returns a simple list.
     */
    public function listSimple()
    {
        Gate::authorize('manage-users'); // Or more specific permission if needed
        return User::select('id', 'name')->orderBy('name')->get();
    }

    public function updateRole(Request $request, User $user)
    {
        Gate::authorize('manage-users');

        $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        // Prevent admin from demoting themselves from the last admin account (optional safeguard)
        $adminRole = Role::where('name', Role::ROLE_ADMIN)->first();
        if ($user->isAdmin() && $user->id === Auth::id() && (int)$request->role_id !== $adminRole->id) {
             // Count other admins
            if (User::where('role_id', $adminRole->id)->count() <= 1) {
                return response()->json(['message' => 'Cannot remove the last administrator role.'], 403);
            }
        }


        $user->role_id = $request->role_id;
        $user->save();

        return response()->json($user->load('role'));
    }

    public function destroy(User $user)
    {
        Gate::authorize('manage-users');

        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'You cannot delete your own account.'], 403);
        }
        if ($user->isAdmin()) {
             $adminRole = Role::where('name', Role::ROLE_ADMIN)->first();
             if (User::where('role_id', $adminRole->id)->count() <= 1) {
                 return response()->json(['message' => 'Cannot delete the last administrator.'], 403);
             }
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully.'], 200);
    }
}