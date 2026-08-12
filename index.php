<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบรับแจ้งเรื่องร้องเรียน - วิทยาลัยอาชีวศึกษามหาสารคาม</title>
    <link rel="icon" href="assets/Logo.png" type="image/png">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Font: Sarabun -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen pb-10">

    <!-- Header / Navbar -->
    <nav class="bg-blue-950 border-b-4 border-amber-500 text-white p-4 shadow-lg">
        <div class="max-w-4xl mx-auto flex justify-between items-center gap-2">
            <!-- Logo & Institution Name -->
            <div class="flex items-center space-x-3">
                <img src="assets/Logo.png" alt="โลโก้วิทยาลัยอาชีวศึกษามหาสารคาม" class="h-12 w-auto bg-white p-1 rounded-full shadow">
                <div>
                    <h1 class="text-base sm:text-lg font-bold leading-tight">วิทยาลัยอาชีวศึกษามหาสารคาม</h1>
                    <p class="text-xs text-amber-400 font-semibold">Mahasarakham Vocational College (MVC)</p>
                </div>
            </div>
            
            <!-- Action Buttons (Admin Login & Quick Exit) -->
            <div class="flex items-center space-x-2">
                <!-- Admin Login Button -->
                <a href="login.php" class="bg-blue-900 hover:bg-blue-800 text-amber-400 border border-amber-500/50 font-bold text-xs px-3 py-2 rounded-lg shadow transition duration-200 flex items-center gap-1.5">
                    <span>🔐</span> <span class="hidden sm:inline">สำหรับเจ้าหน้าที่</span>
                </a>

                <!-- Quick Exit Button -->
                <a href="https://www.google.com" class="bg-red-600 hover:bg-red-700 text-white font-bold text-xs px-3 py-2 rounded-lg shadow transition duration-200 flex items-center gap-1">
                    <span>🚪</span> <span class="hidden sm:inline">ออกด่วน</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="max-w-2xl mx-auto mt-8 px-4">
        <div class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">
            
            <!-- Top Banner Title -->
            <div class="bg-slate-50 p-6 text-center border-b border-slate-200">
                <div class="inline-block bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1 rounded-full mb-2">
                    🔒 ไม่ระบุตัวตน 100%
                </div>
                <h2 class="text-2xl font-bold text-blue-950">ตู้รับแจ้งเรื่องร้องเรียน & ข้อเสนอแนะ</h2>
                <p class="text-sm text-slate-500 mt-1">
                    ระบบงานปกครอง ฝ่ายกิจการนักเรียนนักศึกษา วิทยาลัยอาชีวศึกษามหาสารคาม
                </p>
                <p class="text-xs text-slate-400 mt-0.5">
                    (ระบบไม่บันทึกชื่อ, ไอพีแอดเดรส หรือข้อมูลส่วนตัวของคุณ)
                </p>
                
                <div class="mt-4 pt-3 border-t border-slate-200">
                    <a href="track.php" class="inline-flex items-center text-sm font-semibold text-blue-900 hover:text-amber-600 hover:underline transition">
                        🔍 มี Ticket Code แล้ว? คลิกที่นี่เพื่อติดตามสถานะการดำเนินการ
                    </a>
                </div>
            </div>

            <!-- Complaint Form -->
            <form action="submit.php" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                
                <!-- Category Field -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        หมวดหมู่เรื่องที่ต้องการแจ้ง <span class="text-red-500">*</span>
                    </label>
                    <select name="category" required class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none text-slate-700 bg-white transition">
                        <option value="">-- กรุณาเลือกหมวดหมู่ปัญหา --</option>
                        <option value="การกลั่นแกล้ง / Bullying">การกลั่นแกล้ง / การบุลลี่ (Bullying)</option>
                        <option value="ความปลอดภัย / จุดเสี่ยง">ความปลอดภัย / จุดเสี่ยงภายในวิทยาลัย</option>
                        <option value="อุปกรณ์ชำรุด / ความสะอาด">อาคารสถานที่ / อุปกรณ์ชำรุด / ความสะอาด</option>
                        <option value="พฤติกรรมไม่เหมาะสม">พฤติกรรมไม่เหมาะสม / สารเสพติด / การทะเลาะวิวาท</option>
                        <option value="ข้อเสนอแนะเพื่อพัฒนาวิทยาลัย">ข้อเสนอแนะเพื่อการพัฒนาวิทยาลัย</option>
                        <option value="เรื่องอื่นๆ">เรื่องอื่นๆ</option>
                    </select>
                </div>

                <!-- Details Field -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        รายละเอียดปัญหา / ข้อเสนอแนะ <span class="text-red-500">*</span>
                    </label>
                    <textarea name="details" rows="5" required 
                        placeholder="กรุณาระบุรายละเอียด เช่น เกิดอะไรขึ้น, บริเวณไหนของวิทยาลัย (อาคาร/ชั้น/ห้อง), วันและเวลาที่พบเจอปัญหา..." 
                        class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none text-slate-700 transition"></textarea>
                </div>

                <!-- Image Upload Field -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        แนบรูปภาพประกอบ (ถ้ามี)
                    </label>
                    <input type="file" name="image" id="image-input" accept="image/*" 
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-950 hover:file:bg-blue-100 transition cursor-pointer">
                    <p class="text-xs text-slate-400 mt-1.5 flex items-center gap-1">
                        <span>🛡️</span> ระบบจะทำการลบข้อมูลพิกัดตำแหน่ง (EXIF GPS) ออกจากรูปภาพให้อัตโนมัติเพื่อความปลอดภัยของคุณ
                    </p>

                    <!-- Image Preview -->
                    <div id="image-preview-wrap" class="hidden mt-3 relative inline-block">
                        <img id="image-preview" src="" alt="ตัวอย่างรูปภาพที่เลือก" class="max-h-52 rounded-lg border border-slate-300 shadow-sm">
                        <button type="button" onclick="clearImagePreview()" 
                            class="absolute -top-2 -right-2 bg-red-600 hover:bg-red-700 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold shadow-md">
                            ✕
                        </button>
                        <p id="image-preview-name" class="text-xs text-slate-500 mt-1 truncate"></p>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                    class="w-full bg-amber-500 hover:bg-amber-600 text-blue-950 font-bold py-3.5 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-200 text-base">
                    ✉️ ส่งเรื่องร้องเรียนแบบไม่ระบุตัวตน
                </button>

            </form>
        </div>

        <!-- Footer -->
        <footer class="text-center text-xs text-slate-400 mt-6 space-y-1">
            <p>© วิทยาลัยอาชีวศึกษามหาสารคาม (MVC) - Mahasarakham Vocational College</p>
            <p>พัฒนาระบบเพื่อส่งเสริมความปลอดภัยและคุณภาพชีวิตนักเรียนนักศึกษา</p>
            <p>#Dev By Mak Thitisak</p>
        </footer>
    </div>
    <script>
        const imageInput = document.getElementById('image-input');
        const previewWrap = document.getElementById('image-preview-wrap');
        const previewImg = document.getElementById('image-preview');
        const previewName = document.getElementById('image-preview-name');

        imageInput.addEventListener('change', function () {
            const file = this.files && this.files[0];

            if (!file) {
                clearImagePreview();
                return;
            }

            if (!file.type.startsWith('image/')) {
                alert('กรุณาเลือกไฟล์รูปภาพเท่านั้น');
                clearImagePreview();
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                previewName.textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
                previewWrap.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        });

        function clearImagePreview() {
            imageInput.value = '';
            previewImg.src = '';
            previewName.textContent = '';
            previewWrap.classList.add('hidden');
        }
    </script>

</body>
</html>