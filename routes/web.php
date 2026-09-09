<?php

use App\Events\CanvasUpdated;
use App\Events\ChatMessageEvent;
use App\Events\CursorMovedEvent;
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

    // Otomatis mendeteksi file room.blade.php atau canvas.blade.php agar tidak Error 500
    $viewName = view()->exists('room') ? 'room' : 'canvas';

    return view($viewName, compact('roomId', 'savedCanvas', 'roomInfo'));
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
Route::post('/room/{id}/chat', function ($id, Request $request) {
    $username = $request->input('username') ?: 'Anonim';
    $message = $request->input('message', '');

    broadcast(new ChatMessageEvent($id, $username, $message))->toOthers();

    return response()->json(['status' => 'success']);
});

// 7. Broadcast Cursor Realtime
Route::post('/room/{id}/cursor', function ($id, Request $request) {
    $payload = [
        'id' => $request->header('X-Socket-ID') ?: (string) rand(1000, 9999),
        'username' => $request->input('username') ?: 'User',
        'color' => $request->input('color', '#e63946'),
        'pctX' => $request->input('pctX'),
        'pctY' => $request->input('pctY'),
    ];

    broadcast(new CursorMovedEvent($id, $payload))->toOthers();

    return response()->json(['status' => 'success']);
});
