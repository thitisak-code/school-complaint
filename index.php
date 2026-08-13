<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบรับแจ้งเรื่องร้องเรียน - วิทยาลัยอาชีวศึกษามหาสารคาม</title>
    <link rel="icon" href="assets/Logo.png" type="image/png">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Sarabun (เนื้อหา) + Trirong (หัวเรื่องทางการ) -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Trirong:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .font-formal { font-family: 'Trirong', serif; }

        .paper-bg {
            background-color: #F6F3EC;
            background-image:
                radial-gradient(circle at 1px 1px, rgba(15,27,58,0.045) 1px, transparent 0);
            background-size: 22px 22px;
        }

        .letterhead-rule {
            background-image: repeating-linear-gradient(
                to right,
                #C9A227 0px, #C9A227 6px, transparent 6px, transparent 10px
            );
            height: 2px;
        }

        .doc-card { animation: docRise 0.5s ease-out forwards; opacity: 0; }
        @keyframes docRise {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="paper-bg min-h-screen pb-16 text-slate-800">

    <!-- ░░ Letterhead / Official Header ░░ -->
    <header class="bg-[#0F1B3A] text-white">
        <div class="max-w-3xl mx-auto flex justify-between items-center px-5 py-5 gap-3">
            <!-- Logo & Institution Name -->
            <div class="flex items-center gap-3 min-w-0">
                <img src="assets/Logo.png" alt="ตราวิทยาลัย" class="h-11 w-11 bg-white p-1 rounded-full shadow shrink-0">
                <div class="min-w-0">
                    <h1 class="font-formal font-semibold text-base sm:text-lg leading-tight tracking-wide truncate">วิทยาลัยอาชีวศึกษามหาสารคาม</h1>
                    <p class="text-[11px] text-amber-300/90 tracking-[0.15em] uppercase mt-0.5">Mahasarakham Vocational College</p>
                </div>
            </div>

            <!-- Action Buttons (Admin Login & Quick Exit) -->
            <div class="flex items-center gap-2 shrink-0">
                <a href="login.php" class="border border-amber-400/60 text-amber-300 hover:bg-amber-400 hover:text-blue-950 font-semibold text-xs px-3 py-2 rounded transition duration-200 flex items-center gap-1.5">
                    <span>🔐</span> <span class="hidden sm:inline">สำหรับเจ้าหน้าที่</span>
                </a>
                <a href="https://www.google.com" class="bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs px-3 py-2 rounded transition duration-200 flex items-center gap-1">
                    <span>🚪</span> <span class="hidden sm:inline">ออกด่วน</span>
                </a>
            </div>
        </div>
        <div class="letterhead-rule"></div>
    </header>

    <!-- ░░ Page Title Block ░░ -->
    <div class="max-w-2xl mx-auto px-4">
        <div class="text-center pt-9 pb-6">
            <p class="text-[11px] font-semibold tracking-[0.25em] text-amber-700/80 uppercase mb-2">ระบบราชการ · งานกิจการนักเรียนนักศึกษา</p>
            <h2 class="font-formal text-2xl sm:text-3xl font-semibold text-[#0F1B3A]">ตู้รับแจ้งเรื่องร้องเรียน &amp; ข้อเสนอแนะ</h2>
            <p class="text-sm text-slate-500 mt-2">ระบบงานปกครอง ฝ่ายกิจการนักเรียนนักศึกษา วิทยาลัยอาชีวศึกษามหาสารคาม</p>

            <div class="inline-flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold px-3 py-1.5 rounded-full mt-4">
                🔒 ไม่ระบุตัวตน 100% — ไม่บันทึกชื่อ, ไอพี หรือข้อมูลส่วนตัวของคุณ
            </div>

            <div class="w-16 h-[3px] bg-amber-500 mx-auto mt-5 rounded-full"></div>

            <div class="mt-4">
                <a href="track.php" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0F1B3A] hover:text-amber-600 hover:underline transition">
                    🔍 มี Ticket Code แล้ว? คลิกที่นี่เพื่อติดตามสถานะการดำเนินการ
                </a>
            </div>
        </div>

        <!-- ░░ Complaint Form: Official Document Card ░░ -->
        <div class="doc-card bg-white border border-slate-200 border-t-4 border-t-[#0F1B3A] rounded-lg shadow-md overflow-hidden mb-8">

            <div class="p-5 sm:p-6 border-b-2 border-[#0F1B3A]">
                <p class="text-[11px] font-bold text-slate-400 tracking-[0.2em] uppercase mb-1">แบบฟอร์ม</p>
                <p class="font-formal text-lg font-semibold text-[#0F1B3A]">คำร้องแจ้งเรื่องร้องเรียน / ข้อเสนอแนะ</p>
            </div>

            <form action="submit.php" method="POST" enctype="multipart/form-data" class="p-5 sm:p-6 space-y-6">

                <!-- Category Field -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 tracking-[0.15em] uppercase mb-2">
                        หมวดหมู่เรื่องที่ต้องการแจ้ง <span class="text-rose-500">*</span>
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
                    <label class="block text-[11px] font-bold text-slate-500 tracking-[0.15em] uppercase mb-2">
                        รายละเอียดปัญหา / ข้อเสนอแนะ <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="details" rows="5" required 
                        placeholder="กรุณาระบุรายละเอียด เช่น เกิดอะไรขึ้น, บริเวณไหนของวิทยาลัย (อาคาร/ชั้น/ห้อง), วันและเวลาที่พบเจอปัญหา..." 
                        class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none text-slate-700 transition"></textarea>
                </div>

                <!-- Image Upload Field -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 tracking-[0.15em] uppercase mb-2">
                        แนบรูปภาพประกอบ (ถ้ามี)
                    </label>
                    <input type="file" name="image" id="image-input" accept="image/*" 
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-[#0F1B3A] file:text-amber-400 hover:file:bg-[#16264d] transition cursor-pointer">
                    <p class="text-xs text-slate-400 mt-1.5 flex items-center gap-1">
                        <span>🛡️</span> ระบบจะทำการลบข้อมูลพิกัดตำแหน่ง (EXIF GPS) ออกจากรูปภาพให้อัตโนมัติเพื่อความปลอดภัยของคุณ
                    </p>

                    <!-- Image Preview -->
                    <div id="image-preview-wrap" class="hidden mt-3 relative inline-block">
                        <img id="image-preview" src="" alt="ตัวอย่างรูปภาพที่เลือก" class="max-h-52 rounded-lg border border-slate-300 shadow-sm">
                        <button type="button" onclick="clearImagePreview()" 
                            class="absolute -top-2 -right-2 bg-rose-600 hover:bg-rose-700 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold shadow-md">
                            ✕
                        </button>
                        <p id="image-preview-name" class="text-xs text-slate-500 mt-1 truncate"></p>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                    class="w-full bg-amber-500 hover:bg-amber-600 text-[#0F1B3A] font-bold py-3.5 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-200 text-base">
                    ✉️ ส่งเรื่องร้องเรียนแบบไม่ระบุตัวตน
                </button>

            </form>

            <!-- Footer เอกสาร -->
            <div class="px-5 sm:px-6 py-3 bg-slate-50 border-t border-slate-200 text-[11px] text-slate-400 text-center">
                ข้อมูลที่กรอกจะถูกส่งเข้าสู่ระบบงานปกครองโดยตรง และไม่สามารถระบุตัวตนผู้แจ้งได้
            </div>
        </div>
    </div>

    <!-- ░░ Footer สถาบัน ░░ -->
    <footer class="max-w-2xl mx-auto px-4 mt-4">
        <div class="letterhead-rule mb-4"></div>
        <p class="text-center text-[11px] text-slate-400 leading-relaxed">
            © วิทยาลัยอาชีวศึกษามหาสารคาม (MVC) — Mahasarakham Vocational College<br>
            พัฒนาระบบเพื่อส่งเสริมความปลอดภัยและคุณภาพชีวิตนักเรียนนักศึกษา<br>
            #Dev By Mak Thitisak
        </p>
    </footer>

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