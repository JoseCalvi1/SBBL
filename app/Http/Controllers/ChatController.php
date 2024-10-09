<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function getMessages($articleId)
    {
        Log::info('Article ID: ' . $articleId);
        $messages = ChatMessage::where('article_id', $articleId)->with('user')->orderBy('id', 'DESC')->get();
        return response()->json($messages);
    }

    public function storeMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'article_id' => 'required|exists:articles,id'
        ]);

        $message = ChatMessage::create([
            'user_id' => auth()->id(),
            'article_id' => $request->article_id,
            'message' => $request->message,
        ]);

        return response()->json($message);
    }
}
