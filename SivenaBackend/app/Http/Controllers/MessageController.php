<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Create a new message instance
        $message = new Message();
        $message->name = $validatedData['name'];
        $message->email = $validatedData['email'];
        $message->subject = $validatedData['subject'];
        $message->message = $validatedData['message'];
        
        // Save the message to the database
        $message->save();

        // Optionally, you can return a response to the client
        return response()->json(['message' => 'Message sent successfully'], 201);
    }
}
