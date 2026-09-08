<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>jeio — Collaborative Lobby</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

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
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-between p-4 md:p-8 relative overflow-x-hidden select-none">

    <!-- Header -->
    <header class="w-full max-w-4xl flex items-center justify-between gap-3 mb-8 z-10">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-[#bc6c25] text-[#fefae0] flex items-center justify-center font-black text-3xl border-2 border-[#283618] shadow-[3px_3px_0px_#283618]">
                j
            </div>
            <div>
                <h1 class="text-4xl font-black tracking-wider text-[#283618]">jeio<span class="text-[#bc6c25]">.</span></h1>
                <p class="text-xs font-black text-[#bc6c25] uppercase tracking-wider">Collaborative Drawing Platform</p>
            </div>
        </div>
    </header>

    <!-- Main Grid -->
    <main class="w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 gap-6 z-10 my-auto">
        
        <!-- Form Buat Room -->
        <div class="bg-[#faedcd] neo-box rounded-3xl p-6 flex flex-col justify-between">
            <div>
                <h2 class="text-2xl font-black text-[#283618] uppercase tracking-wider mb-1">➕ Buat Room Baru</h2>
                <p class="text-xs font-bold text-[#bc6c25] mb-5">Atur room menggambar publik atau privat kamu sendiri.</p>

                <form action="/create-room" method="POST" class="flex flex-col gap-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-black uppercase mb-1">Nama Room</label>
                        <input type="text" name="room_name" required placeholder="Nizam & Friends" class="w-full bg-[#fefae0] border-2 border-[#283618] rounded-xl p-3 text-xs font-black text-[#283618] outline-none shadow-[2px_2px_0px_#283618]">
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase mb-1">Maksimal Player</label>
                        <select name="max_players" class="w-full bg-[#fefae0] border-2 border-[#283618] rounded-xl p-3 text-xs font-black text-[#283618] outline-none shadow-[2px_2px_0px_#283618]">
                            <option value="2">2 Orang</option>
                            <option value="4" selected>4 Orang</option>
                            <option value="8">8 Orang</option>
                            <option value="12">12 Orang</option>
                            <option value="99">Unlimited</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase mb-1">Tipe Room</label>
                        <select id="isPrivateSelect" name="is_private" class="w-full bg-[#fefae0] border-2 border-[#283618] rounded-xl p-3 text-xs font-black text-[#283618] outline-none shadow-[2px_2px_0px_#283618]">
                            <option value="0">🌐 Public (Siapa saja bisa gabung)</option>
                            <option value="1">🔒 Private (Pakai Password)</option>
                        </select>
                    </div>

                    <div id="passwordGroup" class="hidden">
                        <label class="block text-xs font-black uppercase mb-1 text-rose-700">Password Room</label>
                        <input type="password" name="password" placeholder="Ketik PIN / Password" class="w-full bg-[#fefae0] border-2 border-[#283618] rounded-xl p-3 text-xs font-black text-[#283618] outline-none shadow-[2px_2px_0px_#283618]">
                    </div>

                    <button type="submit" class="mt-2 w-full bg-[#bc6c25] text-white font-black text-xs py-3.5 rounded-xl neo-btn uppercase tracking-wider">
                        Buat Room Sekarang 🔥
                    </button>
                </form>
            </div>
        </div>

        <!-- Daftar Room Public Active -->
        <div class="bg-[#faedcd] neo-box rounded-3xl p-6 flex flex-col justify-between">
            <div>
                <h2 class="text-2xl font-black text-[#283618] uppercase tracking-wider mb-1">🌐 Public Rooms</h2>
                <p class="text-xs font-bold text-[#bc6c25] mb-5">Gabung langsung ke room public yang lagi aktif.</p>

                <div class="flex flex-col gap-3 max-h-[300px] overflow-y-auto pr-1">
                    <?php $__empty_1 = true; $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="bg-[#fefae0] border-2 border-[#283618] rounded-2xl p-3 flex items-center justify-between shadow-[2px_2px_0px_#283618]">
                            <div>
                                <h3 class="font-black text-sm text-[#283618]"><?php echo e($room['name']); ?></h3>
                                <span class="text-[10px] font-bold text-[#bc6c25]">Max <?php echo e($room['max_players']); ?> Players • Jam <?php echo e($room['created_at']); ?></span>
                            </div>
                            <a href="/room/<?php echo e($room['id']); ?>" class="bg-[#283618] text-[#fefae0] text-xs font-black px-3.5 py-2 rounded-xl neo-btn">
                                Masuk 🚀
                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="bg-[#fefae0] border-2 border-[#283618] rounded-2xl p-4 text-center font-bold text-xs text-[#bc6c25]">
                            Belum ada public room aktif. Buat room baru di sebelah!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </main>

    <footer class="mt-8 text-[11px] font-black text-[#bc6c25] text-center tracking-widest uppercase z-10">
        jeio — Collaborative Drawing
    </footer>

    <script>
        document.getElementById('isPrivateSelect').addEventListener('change', (e) => {
            const passwordGroup = document.getElementById('passwordGroup');
            if (e.target.value === '1') {
                passwordGroup.classList.remove('hidden');
            } else {
                passwordGroup.classList.add('hidden');
            }
        });
    </script>
</body>
</html><?php /**PATH C:\laragon\www\anonimcanvas\resources\views/welcome.blade.php ENDPATH**/ ?>