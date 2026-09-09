<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>jeio — {{ $roomInfo['name'] }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #fefae0;
            color: #283618;
        }
        .neo-box {
            border: 3px solid #283618;
            box-shadow: 5px 5px 0px #283618;
        }
        .neo-btn {
            border: 2px solid #283618;
            box-shadow: 3px 3px 0px #283618;
            transition: all 0.1s ease;
        }
        .neo-btn:active {
            transform: translate(2px, 2px);
            box-shadow: 1px 1px 0px #283618;
        }
        .neo-btn-active {
            background-color: #283618 !important;
            color: #fefae0 !important;
        }

        #chatMessages::-webkit-scrollbar {
            width: 5px;
        }
        #chatMessages::-webkit-scrollbar-thumb {
            background: #bc6c25;
            border-radius: 99px;
            border: 1px solid #283618;
        }

        @keyframes floatUp {
            0% { transform: translateY(0) scale(0.8) rotate(0deg); opacity: 1; }
            50% { transform: translateY(-100px) scale(1.3) rotate(-10deg); }
            100% { transform: translateY(-220px) scale(1) rotate(10deg); opacity: 0; }
        }
        .floating-reaction {
            position: absolute;
            animation: floatUp 2s cubic-bezier(0.25, 1, 0.5, 1) forwards;
            pointer-events: none;
            font-size: 2.2rem;
            z-index: 40;
        }

        .user-cursor {
            position: absolute;
            pointer-events: none;
            z-index: 30;
            transition: left 0.05s linear, top 0.05s linear;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .user-cursor-pointer {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #283618;
            box-shadow: 2px 2px 0px #283618;
        }
        .user-cursor-badge {
            font-size: 10px;
            font-weight: 900;
            padding: 2px 6px;
            border-radius: 6px;
            border: 2px solid #283618;
            box-shadow: 2px 2px 0px #283618;
            white-space: nowrap;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-between p-3 md:p-6 select-none relative overflow-x-hidden">

    <!-- Modal Tampilan Awal Setup Room & Verification Password -->
    <div id="setupModal" class="fixed inset-0 bg-[#283618]/70 backdrop-blur-md z-50 flex items-center justify-center p-4">
        <div class="bg-[#faedcd] neo-box rounded-3xl p-6 md:p-8 w-full max-w-md relative">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-[#bc6c25] text-[#fefae0] flex items-center justify-center font-black text-3xl border-2 border-[#283618] shadow-[3px_3px_0px_#283618]">
                    j
                </div>
                <div>
                    <h2 class="text-3xl font-black text-[#283618] leading-none">{{ $roomInfo['name'] }}</h2>
                    <p class="text-xs font-black text-[#bc6c25] uppercase tracking-wider mt-1">
                        {{ $roomInfo['is_private'] ? '🔒 Private Room' : '🌐 Public Room' }}
                    </p>
                </div>
            </div>

            <form id="setupForm" class="flex flex-col gap-4">
                <div>
                    <label class="block text-xs font-black text-[#283618] uppercase mb-1.5">Username Kamu</label>
                    <input type="text" id="usernameInput" required placeholder="NizamGamer" class="w-full bg-[#fefae0] border-2 border-[#283618] rounded-xl p-3 text-xs font-black text-[#283618] outline-none focus:bg-white shadow-[2px_2px_0px_#283618]">
                </div>

                @if($roomInfo['is_private'])
                <div>
                    <label class="block text-xs font-black text-rose-700 uppercase mb-1.5">Password Room (Required)</label>
                    <input type="password" id="roomPasswordInput" required placeholder="Masukkan Password Room" class="w-full bg-[#fefae0] border-2 border-[#283618] rounded-xl p-3 text-xs font-black text-[#283618] outline-none focus:bg-white shadow-[2px_2px_0px_#283618]">
                    <p id="passwordError" class="text-[11px] font-black text-rose-600 mt-1 hidden"></p>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-black text-[#283618] uppercase mb-1.5">Pilih Warna Kursor</label>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <button type="button" class="cursor-color-opt w-8 h-8 rounded-full bg-[#e63946] border-2 border-[#283618] shadow-[2px_2px_0px_#283618] active:scale-95 transition" data-color="#e63946"></button>
                        <button type="button" class="cursor-color-opt w-8 h-8 rounded-full bg-[#2a9d8f] border-2 border-[#283618] shadow-[2px_2px_0px_#283618] active:scale-95 transition" data-color="#2a9d8f"></button>
                        <button type="button" class="cursor-color-opt w-8 h-8 rounded-full bg-[#e76f51] border-2 border-[#283618] shadow-[2px_2px_0px_#283618] active:scale-95 transition" data-color="#e76f51"></button>
                        <button type="button" class="cursor-color-opt w-8 h-8 rounded-full bg-[#9c89b8] border-2 border-[#283618] shadow-[2px_2px_0px_#283618] active:scale-95 transition" data-color="#9c89b8"></button>
                        <button type="button" class="cursor-color-opt w-8 h-8 rounded-full bg-[#f4a261] border-2 border-[#283618] shadow-[2px_2px_0px_#283618] active:scale-95 transition" data-color="#f4a261"></button>
                        <button type="button" class="cursor-color-opt w-8 h-8 rounded-full bg-[#457b9d] border-2 border-[#283618] shadow-[2px_2px_0px_#283618] active:scale-95 transition" data-color="#457b9d"></button>
                        
                        <label for="customCursorColor" class="cursor-pointer w-8 h-8 rounded-full bg-gradient-to-tr from-red-500 via-green-500 to-blue-500 border-2 border-[#283618] shadow-[2px_2px_0px_#283618] flex items-center justify-center p-[2px]">
                            <span id="customCursorPreview" class="w-full h-full rounded-full bg-[#e63946] border border-white/40"></span>
                            <input type="color" id="customCursorColor" value="#e63946" class="w-0 h-0 opacity-0 absolute">
                        </label>
                    </div>
                </div>

                <button type="submit" class="mt-2 w-full bg-[#bc6c25] hover:bg-[#dda15e] text-[#fefae0] font-black text-sm py-3.5 rounded-xl neo-btn uppercase tracking-wider">
                    Gas Masuk Room 🔥
                </button>
            </form>
        </div>
    </div>

    <!-- Ambient Doodles Background -->
    <div class="fixed inset-0 pointer-events-none z-0 opacity-25 text-[#283618]">
        <svg class="absolute top-6 left-8 w-16 h-16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        <svg class="absolute top-10 right-12 w-20 h-20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
        <svg class="absolute bottom-12 left-10 w-20 h-20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 22h20L12 2zm0 5a1 1 0 110 2 1 1 0 010-2zm-3 6a1 1 0 110 2 1 1 0 010-2zm6 2a1 1 0 110 2 1 1 0 010-2z"></path></svg>
        <svg class="absolute bottom-8 right-10 w-24 h-24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5zM6 9h.01M9 9h.01"></path></svg>
    </div>

    <!-- Header Navbar -->
    <header class="w-full max-w-6xl flex flex-col sm:flex-row items-center justify-between gap-3 mb-4 z-10">
        <div class="flex items-center gap-3">
            <a href="/" class="w-11 h-11 rounded-2xl bg-[#bc6c25] text-[#fefae0] flex items-center justify-center font-black text-3xl border-2 border-[#283618] shadow-[3px_3px_0px_#283618] hover:scale-105 transition">
                j
            </a>
            <div>
                <h1 class="text-3xl font-black tracking-wider text-[#283618]">{{ $roomInfo['name'] }}</h1>
                <p class="text-[10px] font-black text-[#bc6c25] tracking-widest uppercase">
                    Status: <span class="text-[#283618] font-mono">{{ $roomInfo['is_private'] ? '🔒 Private' : '🌐 Public' }}</span> (<span>{{ $roomInfo['max_players'] }} Max</span>)
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 bg-[#faedcd] border-2 border-[#283618] p-1.5 rounded-2xl w-full sm:w-auto shadow-[3px_3px_0px_#283618]">
            <input type="text" id="shareUrl" readonly value="{{ url('/room/' . $roomId) }}" class="bg-transparent text-xs text-[#283618] font-black font-mono px-3 py-1 outline-none w-full sm:w-60 truncate">
            <button id="copyBtn" class="bg-[#bc6c25] hover:bg-[#dda15e] text-white text-xs font-black px-4 py-2 rounded-xl neo-btn">
                Share Link
            </button>
        </div>
    </header>

    <!-- Workspace -->
    <main class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-4 gap-4 items-start z-10">
        
        <!-- Left: Canvas Area & Controls -->
        <div class="lg:col-span-3 flex flex-col items-center gap-3 w-full">
            
            <div id="canvasWrapper" class="neo-box rounded-3xl overflow-hidden relative w-full bg-white cursor-crosshair">
                <canvas id="paintCanvas" width="820" height="480" class="w-full h-auto block" style="touch-action: none;"></canvas>
            </div>

            <!-- Toolbar Minimalis Neubrutalist -->
            <div class="w-full bg-[#faedcd] neo-box p-3 rounded-2xl flex flex-wrap items-center justify-between gap-3">
                
                <!-- Tools -->
                <div class="flex items-center gap-2 border-r-2 border-[#283618]/30 pr-3">
                    <button id="pencilBtn" class="p-2.5 rounded-xl bg-[#283618] text-[#fefae0] neo-btn neo-btn-active" title="Kuas">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </button>
                    <button id="bucketBtn" class="p-2.5 rounded-xl bg-white text-[#283618] neo-btn" title="Fill Bucket">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </button>
                    <button id="eraserBtn" class="p-2.5 rounded-xl bg-white text-[#283618] neo-btn" title="Penghapus">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>

                <!-- Undo & Redo -->
                <div class="flex items-center gap-2 border-r-2 border-[#283618]/30 pr-3">
                    <button id="undoBtn" class="p-2.5 rounded-xl bg-white text-[#283618] neo-btn" title="Undo (Ctrl+Z)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                    </button>
                    <button id="redoBtn" class="p-2.5 rounded-xl bg-white text-[#283618] neo-btn" title="Redo (Ctrl+Y)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"></path></svg>
                    </button>
                </div>

                <!-- Size Slider -->
                <div class="flex items-center gap-2 border-r-2 border-[#283618]/30 pr-3">
                    <input type="range" id="brushSize" min="1" max="40" value="4" class="w-16 accent-[#bc6c25] cursor-pointer" title="Ukuran Kuas">
                    <span id="brushSizePreview" class="w-4 text-center text-xs font-black text-[#283618]">4</span>
                </div>

                <!-- Color Palette -->
                <div class="flex items-center gap-2 flex-wrap">
                    <button class="color-preset w-7 h-7 rounded-full bg-black border-2 border-[#283618] hover:scale-110 transition shadow-[2px_2px_0px_#283618]" data-color="#000000"></button>
                    <button class="color-preset w-7 h-7 rounded-full bg-[#283618] border-2 border-white hover:scale-110 transition shadow-[2px_2px_0px_#283618]" data-color="#283618"></button>
                    <button class="color-preset w-7 h-7 rounded-full bg-[#bc6c25] border-2 border-white hover:scale-110 transition shadow-[2px_2px_0px_#283618]" data-color="#bc6c25"></button>
                    <button class="color-preset w-7 h-7 rounded-full bg-[#dda15e] border-2 border-[#283618] hover:scale-110 transition shadow-[2px_2px_0px_#283618]" data-color="#dda15e"></button>
                    <button class="color-preset w-7 h-7 rounded-full bg-[#e63946] border-2 border-[#283618] hover:scale-110 transition shadow-[2px_2px_0px_#283618]" data-color="#e63946"></button>
                    <button class="color-preset w-7 h-7 rounded-full bg-[#457b9d] border-2 border-[#283618] hover:scale-110 transition shadow-[2px_2px_0px_#283618]" data-color="#457b9d"></button>
                    <button class="color-preset w-7 h-7 rounded-full bg-white border-2 border-[#283618] hover:scale-110 transition shadow-[2px_2px_0px_#283618]" data-color="#ffffff"></button>
                    
                    <div class="relative flex items-center justify-center border-l-2 border-[#283618]/30 pl-2">
                        <label for="customColor" id="wheelCircleLabel" class="cursor-pointer w-7 h-7 rounded-full p-[2px] bg-gradient-to-tr from-red-500 via-green-500 to-blue-500 border-2 border-[#283618] hover:scale-110 active:scale-95 transition shadow-[2px_2px_0px_#283618] flex items-center justify-center" title="Pilih Warna Custom">
                            <span id="wheelColorPreview" class="w-full h-full rounded-full bg-[#283618] transition-colors border border-white/40"></span>
                            <input type="color" id="customColor" value="#283618" class="w-0 h-0 opacity-0 absolute">
                        </label>
                    </div>
                </div>

                <!-- Download & Clear -->
                <div class="flex items-center gap-2 border-l-2 border-[#283618]/30 pl-3 ml-auto">
                    <button id="downloadBtn" class="p-2.5 rounded-xl bg-[#283618] text-[#fefae0] neo-btn" title="Download Canvas PNG">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    </button>
                    <button id="clearBtn" class="p-2.5 rounded-xl bg-rose-200 text-rose-900 neo-btn" title="Bersihkan Kanvas">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Floating Reactions -->
            <div class="w-full bg-[#faedcd] neo-box px-4 py-2 rounded-2xl flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-[#283618]">Reaksi Cepat:</span>
                <div class="flex items-center gap-3">
                    <button class="reaction-btn text-xl hover:scale-125 active:scale-95 transition" data-emoji="🔥">🔥</button>
                    <button class="reaction-btn text-xl hover:scale-125 active:scale-95 transition" data-emoji="😂">😂</button>
                    <button class="reaction-btn text-xl hover:scale-125 active:scale-95 transition" data-emoji="❤️">❤️</button>
                    <button class="reaction-btn text-xl hover:scale-125 active:scale-95 transition" data-emoji="👏">👏</button>
                    <button class="reaction-btn text-xl hover:scale-125 active:scale-95 transition" data-emoji="💩">💩</button>
                </div>
            </div>
        </div>

        <!-- Right: Live Chat -->
        <div class="lg:col-span-1 w-full bg-[#faedcd] neo-box rounded-3xl p-4 flex flex-col justify-between h-[590px]">
            <div>
                <div class="flex items-center justify-between border-b-2 border-[#283618]/30 pb-3 mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-[#bc6c25] animate-ping"></span>
                        <h2 class="font-black text-sm text-[#283618] tracking-wider uppercase">Live Chat</h2>
                    </div>
                    <span id="activeUserLabel" class="text-[10px] font-black text-[#bc6c25] bg-[#fefae0] px-2.5 py-1 rounded-lg border-2 border-[#283618]">User: Anon</span>
                </div>

                <div id="chatMessages" class="flex flex-col gap-2.5 overflow-y-auto h-[440px] pr-1 text-xs">
                    <div class="bg-[#fefae0] border-2 border-[#283618] rounded-xl p-2.5 text-[#283618] font-bold italic shadow-[2px_2px_0px_#283618]">
                        👋 Selamat datang di room <b>{{ $roomInfo['name'] }}</b>! Ketik pesan di bawah buat ngobrol.
                    </div>
                </div>
            </div>

            <form id="chatForm" class="flex gap-2 pt-2 border-t-2 border-[#283618]/30">
                <input type="text" id="chatInput" placeholder="Ketik pesan..." required class="w-full bg-[#fefae0] border-2 border-[#283618] rounded-xl px-3 py-2 text-xs font-black text-[#283618] outline-none focus:bg-white">
                <button type="submit" class="bg-[#bc6c25] text-white font-black text-xs px-4 py-2 rounded-xl neo-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </form>
        </div>

    </main>

    <footer class="mt-4 text-[11px] font-black text-[#bc6c25] text-center tracking-widest uppercase z-10">
        jeio — Collaborative Canvas
    </footer>

    <!-- Logic Script -->
    <script>
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

        function playSound(type) {
            if (audioCtx.state === 'suspended') audioCtx.resume();
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            const now = audioCtx.currentTime;

            if (type === 'pop') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(300, now);
                osc.frequency.exponentialRampToValueAtTime(800, now + 0.08);
                gain.gain.setValueAtTime(0.3, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.08);
                osc.start(now);
                osc.stop(now + 0.08);
            } else if (type === 'ding') {
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(600, now);
                osc.frequency.exponentialRampToValueAtTime(1200, now + 0.15);
                gain.gain.setValueAtTime(0.2, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.15);
                osc.start(now);
                osc.stop(now + 0.15);
            } else if (type === 'clear') {
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(400, now);
                osc.frequency.exponentialRampToValueAtTime(100, now + 0.2);
                gain.gain.setValueAtTime(0.2, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.2);
                osc.start(now);
                osc.stop(now + 0.2);
            } else if (type === 'click') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(400, now);
                gain.gain.setValueAtTime(0.1, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.03);
                osc.start(now);
                osc.stop(now + 0.03);
            }
        }

        const roomId = "{{ $roomId }}";
        const isPrivateRoom = {{ $roomInfo['is_private'] ? 'true' : 'false' }};
        let socketId = null;
        let isSyncing = false;
        let myUsername = "User-" + Math.floor(Math.random() * 899 + 100);
        let myColor = "#e63946";

        document.querySelectorAll('.cursor-color-opt').forEach(btn => {
            btn.addEventListener('click', (e) => {
                myColor = e.target.getAttribute('data-color');
                document.getElementById('customCursorColor').value = myColor;
                document.getElementById('customCursorPreview').style.backgroundColor = myColor;
                playSound('click');
            });
        });

        document.getElementById('customCursorColor').addEventListener('input', (e) => {
            myColor = e.target.value;
            document.getElementById('customCursorPreview').style.backgroundColor = myColor;
        });

        // Setup Modal Form + Validasi Password jika Private Room
        document.getElementById('setupForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const inputName = document.getElementById('usernameInput').value.trim();

            if (isPrivateRoom) {
                const roomPassword = document.getElementById('roomPasswordInput').value;
                const response = await fetch('/room/' + roomId + '/verify-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ password: roomPassword })
                });

                const resData = await response.json();
                if (resData.status === 'error') {
                    const errEl = document.getElementById('passwordError');
                    errEl.innerText = resData.message;
                    errEl.classList.remove('hidden');
                    return;
                }
            }

            if (inputName) myUsername = inputName;

            document.getElementById('activeUserLabel').innerText = myUsername;
            document.getElementById('setupModal').classList.add('hidden');
            playSound('click');
        });

        // Canvas Setup
        const canvas = document.getElementById('paintCanvas');
        const ctx = canvas.getContext('2d', { willReadFrequently: true });

        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        let isDrawing = false;
        let currentMode = 'pencil';
        let currentColor = '#283618';
        let currentSize = 4;

        let historyStack = [];
        let redoStack = [];
        const MAX_HISTORY = 20;

        function saveHistory() {
            if (historyStack.length >= MAX_HISTORY) historyStack.shift();
            historyStack.push(ctx.getImageData(0, 0, canvas.width, canvas.height));
            redoStack = [];
        }

        saveHistory();

        @if(isset($savedCanvas) && $savedCanvas)
            isSyncing = true;
            const img = new Image();
            img.onload = function() {
                ctx.drawImage(img, 0, 0);
                saveHistory();
                isSyncing = false;
            };
            img.src = {!! $savedCanvas !!}.imgData || '';
        @endif

        const PUSHER_APP_KEY = "{{ config('broadcasting.connections.pusher.key') }}";
        const PUSHER_CLUSTER = "{{ config('broadcasting.connections.pusher.options.cluster', 'ap1') }}";
        const pusher = new Pusher(PUSHER_APP_KEY, { cluster: PUSHER_CLUSTER });

        pusher.connection.bind('connected', function() {
            socketId = pusher.connection.socket_id;
        });

        const channel = pusher.subscribe('canvas-room.' + roomId);

        channel.bind('canvas.updated', function(data) {
            isSyncing = true;
            const img = new Image();
            img.onload = function() {
                ctx.drawImage(img, 0, 0);
                saveHistory();
                isSyncing = false;
            };
            img.src = data.imgData;
        });

        channel.bind('chat.sent', function(data) {
            const sender = data.username || data.user || data.name || 'Anonim';
            const msg = data.message || data.text || '';
            appendChatMessage(sender, msg, false);
            playSound('ding');
        });

        channel.bind('cursor.moved', function(data) {
            updateRemoteCursor(data);
        });

        function syncCanvas() {
            if (isSyncing) return;
            const imgData = canvas.toDataURL();

            fetch('/room/' + roomId + '/broadcast', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Socket-ID': socketId
                },
                body: JSON.stringify({ imgData: imgData })
            });
        }

        function getCanvasCoords(e) {
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            return {
                x: Math.floor((e.clientX - rect.left) * scaleX),
                y: Math.floor((e.clientY - rect.top) * scaleY),
                pctX: ((e.clientX - rect.left) / rect.width) * 100,
                pctY: ((e.clientY - rect.top) / rect.height) * 100
            };
        }

        let lastCursorSend = 0;
        
        // Menggunakan Pointer Event agar mendukung sentuhan jari HP & klik Mouse PC
        canvas.addEventListener('pointermove', (e) => {
            const coords = getCanvasCoords(e);
            const now = Date.now();

            if (now - lastCursorSend > 40) {
                lastCursorSend = now;
                fetch('/room/' + roomId + '/cursor', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Socket-ID': socketId
                    },
                    body: JSON.stringify({
                        id: socketId,
                        username: myUsername,
                        color: myColor,
                        pctX: coords.pctX,
                        pctY: coords.pctY
                    })
                });
            }

            if (!isDrawing) return;

            ctx.lineWidth = currentSize;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = (currentMode === 'eraser') ? '#ffffff' : currentColor;

            ctx.lineTo(coords.x, coords.y);
            ctx.stroke();
        });

        canvas.addEventListener('pointerdown', (e) => {
            e.preventDefault();
            const coords = getCanvasCoords(e);

            if (currentMode === 'bucket') {
                floodFill(coords.x, coords.y, hexToRgb(currentColor));
                playSound('pop');
                saveHistory();
                syncCanvas();
            } else {
                playSound('click');
                isDrawing = true;
                ctx.beginPath();
                ctx.moveTo(coords.x, coords.y);
            }
        });

        window.addEventListener('pointerup', () => {
            if (isDrawing) {
                isDrawing = false;
                ctx.closePath();
                saveHistory();
                syncCanvas();
            }
        });

        const remoteCursors = {};
        function updateRemoteCursor(data) {
            const wrapper = document.getElementById('canvasWrapper');
            let cursorEl = remoteCursors[data.id];

            if (!cursorEl) {
                cursorEl = document.createElement('div');
                cursorEl.className = 'user-cursor';
                cursorEl.innerHTML = `
                    <div class="user-cursor-pointer" style="background-color: ${data.color}"></div>
                    <div class="user-cursor-badge" style="background-color: ${data.color}; color: #ffffff;">${data.username}</div>
                `;
                wrapper.appendChild(cursorEl);
                remoteCursors[data.id] = cursorEl;
            } else {
                cursorEl.querySelector('.user-cursor-pointer').style.backgroundColor = data.color;
                const badge = cursorEl.querySelector('.user-cursor-badge');
                badge.style.backgroundColor = data.color;
                badge.innerText = data.username;
            }

            cursorEl.style.left = data.pctX + '%';
            cursorEl.style.top = data.pctY + '%';

            clearTimeout(cursorEl.timeout);
            cursorEl.style.opacity = '1';
            cursorEl.timeout = setTimeout(() => { cursorEl.style.opacity = '0'; }, 3000);
        }

        function doUndo() {
            if (historyStack.length > 1) {
                redoStack.push(historyStack.pop());
                const previousState = historyStack[historyStack.length - 1];
                ctx.putImageData(previousState, 0, 0);
                playSound('click');
                syncCanvas();
            }
        }

        function doRedo() {
            if (redoStack.length > 0) {
                const nextState = redoStack.pop();
                historyStack.push(nextState);
                ctx.putImageData(nextState, 0, 0);
                playSound('click');
                syncCanvas();
            }
        }

        document.getElementById('undoBtn').addEventListener('click', doUndo);
        document.getElementById('redoBtn').addEventListener('click', doRedo);

        window.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                if (e.key === 'z' || e.key === 'Z') {
                    e.preventDefault();
                    doUndo();
                } else if (e.key === 'y' || e.key === 'Y') {
                    e.preventDefault();
                    doRedo();
                }
            }
        });

        function floodFill(startX, startY, fillColor) {
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const data = imageData.data;

            const startPos = (startY * canvas.width + startX) * 4;
            const startR = data[startPos];
            const startG = data[startPos + 1];
            const startB = data[startPos + 2];
            const startA = data[startPos + 3];

            if (matchColor(data, startPos, fillColor)) return;

            const pixelStack = [[startX, startY]];

            while (pixelStack.length) {
                const newPos = pixelStack.pop();
                const x = newPos[0];
                let y = newPos[1];

                let pixelPos = (y * canvas.width + x) * 4;

                while (y >= 0 && matchStartColor(data, pixelPos, startR, startG, startB, startA)) {
                    y--;
                    pixelPos -= canvas.width * 4;
                }

                pixelPos += canvas.width * 4;
                y++;

                let reachLeft = false;
                let reachRight = false;

                while (y < canvas.height && matchStartColor(data, pixelPos, startR, startG, startB, startA)) {
                    colorPixel(data, pixelPos, fillColor);

                    if (x > 0) {
                        if (matchStartColor(data, pixelPos - 4, startR, startG, startB, startA)) {
                            if (!reachLeft) {
                                pixelStack.push([x - 1, y]);
                                reachLeft = true;
                            }
                        } else if (reachLeft) {
                            reachLeft = false;
                        }
                    }

                    if (x < canvas.width - 1) {
                        if (matchStartColor(data, pixelPos + 4, startR, startG, startB, startA)) {
                            if (!reachRight) {
                                pixelStack.push([x + 1, y]);
                                reachRight = true;
                            }
                        } else if (reachRight) {
                            reachRight = false;
                        }
                    }

                    y++;
                    pixelPos += canvas.width * 4;
                }
            }

            ctx.putImageData(imageData, 0, 0);
        }

        function matchStartColor(data, pos, r, g, b, a) {
            return Math.abs(data[pos] - r) < 30 &&
                   Math.abs(data[pos + 1] - g) < 30 &&
                   Math.abs(data[pos + 2] - b) < 30 &&
                   Math.abs(data[pos + 3] - a) < 30;
        }

        function matchColor(data, pos, color) {
            return data[pos] === color.r && data[pos + 1] === color.g && data[pos + 2] === color.b;
        }

        function colorPixel(data, pos, color) {
            data[pos] = color.r;
            data[pos + 1] = color.g;
            data[pos + 2] = color.b;
            data[pos + 3] = 255;
        }

        function hexToRgb(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : { r: 0, g: 0, b: 0 };
        }

        const pencilBtn = document.getElementById('pencilBtn');
        const bucketBtn = document.getElementById('bucketBtn');
        const eraserBtn = document.getElementById('eraserBtn');

        function setActiveTool(btn, mode) {
            currentMode = mode;
            [pencilBtn, bucketBtn, eraserBtn].forEach(b => {
                b.classList.remove('bg-[#283618]', 'text-[#fefae0]', 'neo-btn-active');
                b.classList.add('bg-white', 'text-[#283618]');
            });
            btn.classList.remove('bg-white');
            btn.classList.add('bg-[#283618]', 'text-[#fefae0]', 'neo-btn-active');
            playSound('click');
        }

        pencilBtn.addEventListener('click', () => setActiveTool(pencilBtn, 'pencil'));
        bucketBtn.addEventListener('click', () => setActiveTool(bucketBtn, 'bucket'));
        eraserBtn.addEventListener('click', () => setActiveTool(eraserBtn, 'eraser'));

        document.getElementById('brushSize').addEventListener('input', (e) => {
            currentSize = parseInt(e.target.value);
            document.getElementById('brushSizePreview').innerText = currentSize;
        });

        function updateColorPreview(color) {
            currentColor = color;
            document.getElementById('customColor').value = color;
            document.getElementById('wheelColorPreview').style.backgroundColor = color;
            playSound('click');
        }

        document.querySelectorAll('.color-preset').forEach(btn => {
            btn.addEventListener('click', (e) => {
                updateColorPreview(e.target.getAttribute('data-color'));
            });
        });

        document.getElementById('customColor').addEventListener('input', (e) => {
            updateColorPreview(e.target.value);
        });

        document.getElementById('downloadBtn').addEventListener('click', () => {
            playSound('pop');
            const imageURI = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            const dateStr = new Date().toISOString().slice(0, 10);
            
            link.download = `jeio-artwork-${roomId}-${dateStr}.png`;
            link.href = imageURI;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        document.getElementById('clearBtn').addEventListener('click', () => {
            if (confirm('Bersihkan seluruh kanvas jeio?')) {
                ctx.fillStyle = "#ffffff";
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                playSound('clear');
                saveHistory();
                syncCanvas();
            }
        });

        document.querySelectorAll('.reaction-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const emoji = e.target.getAttribute('data-emoji');
                spawnFloatingEmoji(emoji);
                playSound('pop');
            });
        });

        function spawnFloatingEmoji(emoji) {
            const wrapper = document.getElementById('canvasWrapper');
            const reactionEl = document.createElement('div');
            reactionEl.className = 'floating-reaction';
            reactionEl.innerText = emoji;

            const randomX = Math.floor(Math.random() * (wrapper.offsetWidth - 60)) + 30;
            reactionEl.style.left = randomX + 'px';
            reactionEl.style.bottom = '20px';

            wrapper.appendChild(reactionEl);

            setTimeout(() => { reactionEl.remove(); }, 2000);
        }

        document.getElementById('copyBtn').addEventListener('click', function() {
            const copyText = document.getElementById("shareUrl");
            copyText.select();
            navigator.clipboard.writeText(copyText.value);
            this.innerText = "Tersalin! 🔥";
            playSound('click');
            setTimeout(() => { this.innerText = "Share Link"; }, 2000);
        });

        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const chatMessages = document.getElementById('chatMessages');

        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const message = chatInput.value.trim();
            if (!message) return;

            appendChatMessage(myUsername, message, true);
            playSound('ding');

            fetch('/room/' + roomId + '/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Socket-ID': socketId
                },
                body: JSON.stringify({
                    username: myUsername,
                    message: message
                })
            });

            chatInput.value = '';
        });

        function appendChatMessage(user, msg, isSelf) {
            const displayUser = user || 'Anonim';
            const displayMsg = msg || '';

            const msgDiv = document.createElement('div');
            msgDiv.className = isSelf 
                ? "bg-[#bc6c25] text-white font-bold rounded-xl p-2.5 self-end ml-4 border-2 border-[#283618] shadow-[2px_2px_0px_#283618]"
                : "bg-[#fefae0] border-2 border-[#283618] rounded-xl p-2.5 text-[#283618] font-bold self-start mr-4 shadow-[2px_2px_0px_#283618]";

            msgDiv.innerHTML = `<span class="font-black ${isSelf ? 'text-[#faedcd]' : 'text-[#bc6c25]'} block text-[10px] uppercase">${displayUser}</span><span>${displayMsg}</span>`;
            
            chatMessages.appendChild(msgDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    </script>
</body>
</html>
