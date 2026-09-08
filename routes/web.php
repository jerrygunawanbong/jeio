<?php

use App\Events\CanvasUpdated;
use App\Events\ChatMessageSent;
use App\Events\CursorMoved;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

// 1. Halaman Utama Lobby (Menampilkan Pilihan Buat Room Public/Private & Daftar Public Room)
Route::get('/', function () {
    $rooms = Cache::get('jeio_public_rooms', []);
    return view('welcome', compact('rooms'));
});

// 2. Endpoint Proses Pembuatan Room Baru
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

    // Simpan Info Room ke Cache
    Cache::put('room_info_' . $roomId, $roomData);

    // Kalau Room Public, daftarkan ke list lobby public
    if (!$isPrivate) {
        $publicRooms = Cache::get('jeio_public_rooms', []);
        $publicRooms[$roomId] = $roomData;
        Cache::put('jeio_public_rooms', $publicRooms);
    }

    return redirect('/room/' . $roomId);
});

// 3. Halaman Canvas Room
Route::get('/room/{roomId}', function ($roomId) {
    $roomInfo = Cache::get('room_info_' . $roomId, [
        'id' => $roomId,
        'name' => 'Room ' . strtoupper($roomId),
        'is_private' => false,
        'password' => null,
        'max_players' => 4
    ]);

    $savedCanvas = Cache::get('canvas_room_' . $roomId, null);
    return view('canvas', compact('roomId', 'savedCanvas', 'roomInfo'));
});

// 4. Verification Password untuk Private Room
Route::post('/room/{roomId}/verify-password', function (Request $request, $roomId) {
    $roomInfo = Cache::get('room_info_' . $roomId);
    $inputPassword = $request->input('password');

    if ($roomInfo && $roomInfo['is_private'] && $roomInfo['password'] !== $inputPassword) {
        return response()->json(['status' => 'error', 'message' => 'Password room salah!']);
    }

    return response()->json(['status' => 'success']);
});

// 5. Broadcast Canvas Realtime
Route::post('/room/{roomId}/broadcast', function (Request $request, $roomId) {
    $data = $request->json()->all();
    Cache::put('canvas_room_' . $roomId, json_encode($data));
    broadcast(new CanvasUpdated($data, $roomId))->toOthers();
    return response()->json(['status' => 'success']);
});

// 6. Broadcast Chat Realtime
Route::post('/room/{roomId}/chat', function (Request $request, $roomId) {
    $messageData = $request->json()->all();
    broadcast(new ChatMessageSent($messageData, $roomId))->toOthers();
    return response()->json(['status' => 'success']);
});

// 7. Broadcast Cursor Realtime
Route::post('/room/{roomId}/cursor', function (Request $request, $roomId) {
    $cursorData = $request->json()->all();
    broadcast(new CursorMoved($cursorData, $roomId))->toOthers();
    return response()->json(['status' => 'success']);
});