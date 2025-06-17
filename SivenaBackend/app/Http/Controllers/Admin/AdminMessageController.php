<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class AdminMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('manage-messages');

        $query = Message::query();

        // Search (adapt based on which fields you want to search)
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm)
                  ->orWhere('subject', 'like', $searchTerm)
                  ->orWhere('message', 'like', $searchTerm); // Ensure 'message' column exists
            });
        }

        // Filter by read status
        if ($request->has('is_read') && $request->input('is_read') !== null) {
            $isReadValue = in_array($request->input('is_read'), ['true', '1'], true);
            $query->where('is_read', $isReadValue);
        }

        // Sorting
        $sortBy = $request->input('sortBy', 'created_at');
        $sortDesc = $request->input('sortDesc', 'true') === 'true' ? 'desc' : 'asc';

        $allowedSorts = ['id', 'name', 'email', 'subject', 'created_at', 'is_read'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDesc);
        } else {
            $query->orderBy('created_at', 'desc'); // Default sort
        }

        $perPage = $request->input('perPage', 15);
        $messages = $query->paginate($perPage);

        return response()->json($messages);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message)
    {
        Gate::authorize('manage-messages');

        try {
            $messageName = $message->name; // For logging
            $message->delete();
            Log::info("Admin (ID: " . Auth::id() . ") deleted message from: " . $messageName);
            return response()->json(['message' => 'Message deleted successfully.'], 200);
        } catch (\Exception $e) {
            Log::error("Error deleting message ID {$message->id}: " . $e->getMessage());
            return response()->json(['message' => 'Failed to delete message.'], 500);
        }
    }

    /**
     * Mark the specified message as read.
     */
    public function markAsRead(Message $message)
    {
        Gate::authorize('manage-messages');
        $message->update(['is_read' => true]);
        return response()->json(['message' => 'Message marked as read.', 'data' => $message]);
    }

    /**
     * Mark the specified message as unread.
     */
    public function markAsUnread(Message $message)
    {
        Gate::authorize('manage-messages');
        $message->update(['is_read' => false]);
        return response()->json(['message' => 'Message marked as unread.', 'data' => $message]);
    }

    /**
     * Remove multiple messages from storage.
     */
    public function bulkDelete(Request $request)
    {
        Gate::authorize('manage-messages');

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:messages,id', // Validate each ID
        ]);

        try {
            $count = Message::whereIn('id', $validated['ids'])->delete();
            Log::info("Admin (ID: " . Auth::id() . ") bulk deleted " . $count . " messages: " . implode(', ', $validated['ids']));
            return response()->json(['message' => $count . ' message(s) deleted successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Error during bulk message delete: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete selected messages.'], 500);
        }
    }
}