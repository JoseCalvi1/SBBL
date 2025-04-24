<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function getMessages($eventId)
    {
        Log::info('Event ID: ' . $eventId);
        $messages = ChatMessage::where('event_id', $eventId)->with('user')->orderBy('id', 'DESC')->get();
        return response()->json($messages);
    }

    public function storeMessage(Request $request)
    {
        Log::info('Request data:', $request->all());
        $request->validate([
            'message' => 'required|string',
            'event_id' => 'required|exists:events,id'
        ]);

        $message = ChatMessage::create([
            'user_id' => auth()->id(),
            'event_id' => $request->event_id,
            'message' => $request->message,
        ]);

        return response()->json($message);
    }
}
