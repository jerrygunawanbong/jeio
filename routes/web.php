<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

function triggerPusherDirect($channel, $event, $data, $socketId = null) {
    $key = config('broadcasting.connections.pusher.key') ?: env('PUSHER_APP_KEY');
    $secret = config('broadcasting.connections.pusher.secret') ?: env('PUSHER_APP_SECRET');
    $appId = config('broadcasting.connections.pusher.app_id') ?: env('PUSHER_APP_ID');
    $cluster = config('broadcasting.connections.pusher.options.cluster') ?: env('PUSHER_APP_CLUSTER', 'ap1');

    if ($key && $secret && $appId) {
        $pusher = new \Pusher\Pusher($key, $secret, $appId, [
            'cluster' => $cluster,
            'useTLS' => true
        ]);
        
        $params = $socketId ? ['socket_id' => $socketId] : [];
        $pusher->trigger($channel, $event, $data, $params);
    }
}

// Auth Endpoint untuk Private Channel Pusher Client Events
Route::post('/broadcasting/auth', function (Request $request) {
    $key = config('broadcasting.connections.pusher.key') ?: env('PUSHER_APP_KEY');
    $secret = config('broadcasting.connections.pusher.secret') ?: env('PUSHER_APP_SECRET');
    $appId = config('broadcasting.connections.pusher.app_id') ?: env('PUSHER_APP_ID');
    $cluster = config('broadcasting.connections.pusher.options.cluster') ?: env('PUSHER_APP_CLUSTER', 'ap1');

    $pusher = new \Pusher\Pusher($key, $secret, $appId, ['cluster' => $cluster, 'useTLS' => true]);
    $auth = $pusher->authorizeChannel($request->input('channel_name'), $request->input('socket_id'));
    
    return response($auth);
});

// 1. Lobby
Route::get('/', function () {
    $rooms = Cache::get('jeio_public_rooms', []);
    return view('welcome', compact('rooms'));
});

// 2. Buat Room
Route::post('/create-room', function (Request $request) {
    $roomId = Str::random(8);
    $isPrivate = $request->input('is_private') === '1';
    $password = $request->input('password');

    $roomData = [
        'id' => $roomId,
        'name' => $request->input('room_name') ?: 'Room ' . strtoupper($roomId),
        'is_private' => $isPrivate,
        'password' => $isPrivate ? $password : null,
        'max_players' => $request->input('max_players', 4),
        'created_at' => now()->format('H:i')
    ];

    Cache::put('room_info_' . $roomId, $roomData);

    if (!$isPrivate) {
        $publicRooms = Cache::get('jeio_public_rooms', []);
        $publicRooms[$roomId] = $roomData;
        Cache::put('jeio_public_rooms', $publicRooms);
    }

    return redirect('/room/' . $roomId);
});

// 3. Canvas Room
Route::get('/room/{roomId}', function ($roomId) {
    $roomInfo = Cache::get('room_info_' . $roomId, [
        'id' => $roomId,
        'name' => 'Room ' . strtoupper($roomId),
        'is_private' => false,
        'password' => null,
        'max_players' => 4
    ]);

    $savedCanvas = Cache::get('canvas_room_' . $roomId, null);
    $viewName = view()->exists('canvas') ? 'canvas' : 'room';

    return view($viewName, compact('roomId', 'savedCanvas', 'roomInfo'));
});

// 4. Verify Password
Route::post('/room/{roomId}/verify-password', function (Request $request, $roomId) {
    $roomInfo = Cache::get('room_info_' . $roomId);
    $inputPassword = $request->input('password');

    if ($roomInfo && $roomInfo['is_private'] && $roomInfo['password'] !== $inputPassword) {
        return response()->json(['status' => 'error', 'message' => 'Password room salah!']);
    }

    return response()->json(['status' => 'success']);
});

// 5. Broadcast Canvas Base64 (Untuk simpan state di cache/room)
Route::post('/room/{roomId}/broadcast', function (Request $request, $roomId) {
    $data = $request->json()->all();
    $socketId = $request->header('X-Socket-ID');

    Cache::put('canvas_room_' . $roomId, json_encode($data));
    triggerPusherDirect('private-canvas-room.' . $roomId, 'canvas.updated', $data, $socketId);

    return response()->json(['status' => 'success']);
});

// 6. Broadcast Chat
Route::post('/room/{id}/chat', function ($id, Request $request) {
    $username = $request->input('username') ?: 'Anonim';
    $message = $request->input('message', '');
    $socketId = $request->header('X-Socket-ID');

    $payload = ['username' => $username, 'message' => $message];
    triggerPusherDirect('private-canvas-room.' . $id, 'chat.sent', $payload, $socketId);

    return response()->json(['status' => 'success']);
});
