<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message; // Assuming your model is App\Models\Message
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('manage-messages');

        $query = Message::query();

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm)
                  ->orWhere('subject', 'like', $searchTerm)
                  ->orWhere('message_content', 'like', $searchTerm); // 'message_content' or just 'message' from your migration
            });
        }

        $sortBy = $request->input('sortBy', 'created_at');
        $sortDesc = $request->input('sortDesc', 'true') === 'true' ? 'desc' : 'asc';

        $allowedSorts = ['name', 'email', 'subject', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDesc);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $messages = $query->paginate($request->input('perPage', 15));
        return response()->json($messages);
    }


    /**
     * Display the specified resource. (Optional: for a detailed view if not using expanded row)
     */
    // public function show(Message $message)
    // {
    //     Gate::authorize('manage-messages');
    //     // Mark as read if you have such functionality
    //     // if (!$message->is_read) {
    //     //     $message->is_read = true;
    //     //     $message->save();
    //     // }
    //     return response()->json($message);
    // }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message) // Route model binding
    {
        Gate::authorize('manage-messages');

        $message->delete();
        return response()->json(['message' => 'Message deleted successfully.'], 200);
    }
}