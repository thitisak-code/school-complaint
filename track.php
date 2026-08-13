<?php
require_once __DIR__ . '/config/db.php';

$ticket_code = isset($_GET['code']) ? trim($_GET['code']) : '';
$complaint   = null;
$error       = '';

if (!empty($ticket_code)) {
    $stmt = $pdo->prepare("SELECT * FROM complaints WHERE ticket_code = :code");
    $stmt->execute(['code' => $ticket_code]);
    $complaint = $stmt->fetch();

    if (!$complaint) {
        $error = 'ไม่พบข้อมูล Ticket Code นี้ในระบบ กรุณาตรวจสอบรหัสอีกครั้ง';
    }
}

// กำหนดลำดับขั้นตอนการดำเนินการ (ใช้แสดงผลเป็น Stepper อย่างเป็นทางการ)
$step_index = 0; // 0 = รับเรื่อง, 1 = ตรวจสอบ, 2 = เสร็จสิ้น
if ($complaint) {
    switch ($complaint['status']) {
        case 'pending':     $step_index = 0; break;
        case 'in_progress': $step_index = 1; break;
        case 'resolved':    $step_index = 2; break;
    }
}

$status_meta = [
    'pending'     => ['label' => 'รอดำเนินการ',            'icon' => '⏳', 'ring' => 'border-amber-400', 'text' => 'text-amber-700', 'bg' => 'bg-amber-50'],
    'in_progress' => ['label' => 'กำลังตรวจสอบ',           'icon' => '🔎', 'ring' => 'border-sky-500',   'text' => 'text-sky-700',   'bg' => 'bg-sky-50'],
    'resolved'    => ['label' => 'ดำเนินการแล้วเสร็จ',      'icon' => '✓',  'ring' => 'border-emerald-600','text' => 'text-emerald-700','bg' => 'bg-emerald-50'],
    'rejected'    => ['label' => 'ยุติเรื่อง / ข้อมูลไม่เพียงพอ', 'icon' => '✕', 'ring' => 'border-rose-500', 'text' => 'text-rose-700', 'bg' => 'bg-rose-50'],
];
$meta = $complaint ? $status_meta[$complaint['status']] : null;
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดตามสถานะ - วิทยาลัยอาชีวศึกษามหาสารคาม</title>
    <link rel="icon" href="assets/Logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
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

        /* ตราประทับสถานะ (Official Stamp) */
        .status-stamp {
            border: 3px double currentColor;
            transform: rotate(-4deg);
        }

        /* เส้นเชื่อม Stepper */
        .step-line { background-color: #D8D2C4; }
        .step-line.filled { background-color: #0F1B3A; }

        .step-dot {
            border: 2px solid #D8D2C4;
            background: #fff;
            color: #A8A196;
        }
        .step-dot.done {
            border-color: #0F1B3A;
            background: #0F1B3A;
            color: #fff;
        }
        .step-dot.current {
            border-color: #C9A227;
            background: #C9A227;
            color: #0F1B3A;
        }
    </style>
</head>
<body class="paper-bg min-h-screen pb-16 text-slate-800">

    <!-- ░░ Letterhead / Official Header ░░ -->
    <header class="bg-[#0F1B3A] text-white">
        <div class="max-w-3xl mx-auto flex justify-between items-center px-5 py-5 gap-4">
            <div class="flex items-center gap-3">
                <img src="assets/Logo.png" alt="ตราวิทยาลัย" class="h-11 w-11 bg-white p-1 rounded-full shadow shrink-0">
                <div>
                    <h1 class="font-formal font-semibold text-base sm:text-lg leading-tight tracking-wide">วิทยาลัยอาชีวศึกษามหาสารคาม</h1>
                    <p class="text-[11px] text-amber-300/90 tracking-[0.15em] uppercase mt-0.5">Mahasarakham Vocational College</p>
                </div>
            </div>
            <a href="index.php" class="shrink-0 border border-amber-400/60 text-amber-300 hover:bg-amber-400 hover:text-blue-950 font-semibold text-xs px-3.5 py-2 rounded transition duration-200">
                ＋ แจ้งเรื่องใหม่
            </a>
        </div>
        <div class="letterhead-rule"></div>
    </header>

    <!-- ░░ Page Title Block ░░ -->
    <div class="max-w-2xl mx-auto px-4">
        <div class="text-center pt-9 pb-6">
            <p class="text-[11px] font-semibold tracking-[0.25em] text-amber-700/80 uppercase mb-2">ระบบราชการ · งานกิจการนักเรียนนักศึกษา</p>
            <h2 class="font-formal text-2xl sm:text-3xl font-semibold text-[#0F1B3A]">ติดตามสถานะการดำเนินการ</h2>
            <p class="text-xs text-slate-500 mt-2">โปรดกรอกเลขที่อ้างอิง (Ticket Code) ที่ได้รับไว้ ณ ขณะแจ้งเรื่อง เพื่อตรวจสอบความคืบหน้า</p>
            <div class="w-16 h-[3px] bg-amber-500 mx-auto mt-4 rounded-full"></div>
        </div>

        <!-- ░░ Search Form ░░ -->
        <form action="track.php" method="GET" class="bg-white border border-slate-200 border-t-4 border-t-[#0F1B3A] rounded-lg shadow-sm p-5 mb-6">
            <label class="block text-xs font-bold text-slate-600 mb-2 tracking-wide">เลขที่อ้างอิง (TICKET CODE)</label>
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" name="code" value="<?php echo htmlspecialchars($ticket_code); ?>" placeholder="เช่น TK-8A2F1B" required
                    class="flex-1 p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none font-mono text-center text-lg tracking-widest uppercase text-[#0F1B3A]">
                <button type="submit" class="bg-[#0F1B3A] hover:bg-[#16264d] text-amber-400 font-bold px-6 py-3 rounded-lg transition duration-200 shrink-0">
                    🔍 ตรวจสอบสถานะ
                </button>
            </div>
        </form>

        <?php if ($error): ?>
            <div class="doc-card bg-white border border-rose-200 border-l-4 border-l-rose-500 rounded-lg shadow-sm p-5 mb-6">
                <div class="flex items-start gap-3">
                    <span class="text-rose-500 text-xl leading-none">⚠</span>
                    <div>
                        <p class="font-semibold text-rose-700 text-sm">ไม่พบข้อมูลในระบบ</p>
                        <p class="text-xs text-slate-500 mt-1"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($complaint): ?>
            <!-- ░░ Official Result Document ░░ -->
            <div class="doc-card bg-white border border-slate-200 rounded-lg shadow-md overflow-hidden mb-8">

                <!-- Memo-style header: เรื่อง / เลขที่อ้างอิง -->
                <div class="p-5 sm:p-6 border-b-2 border-[#0F1B3A]">
                    <div class="flex flex-wrap justify-between items-start gap-4">
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 tracking-[0.2em] uppercase mb-1">เรื่อง</p>
                            <p class="font-formal text-lg font-semibold text-[#0F1B3A]"><?php echo htmlspecialchars($complaint['category']); ?></p>
                            <p class="text-[11px] text-slate-400 mt-2">
                                เลขที่อ้างอิง
                                <span class="font-mono font-bold text-indigo-700 tracking-wider ml-1"><?php echo htmlspecialchars($complaint['ticket_code']); ?></span>
                            </p>
                        </div>

                        <!-- ตราประทับสถานะ -->
                        <div class="status-stamp <?php echo $meta['ring']; ?> <?php echo $meta['text']; ?> rounded-lg px-3.5 py-2 text-center shrink-0">
                            <div class="text-xl leading-none"><?php echo $meta['icon']; ?></div>
                            <div class="text-[10px] font-extrabold tracking-wide mt-0.5 whitespace-nowrap"><?php echo $meta['label']; ?></div>
                        </div>
                    </div>
                </div>

                <div class="p-5 sm:p-6 space-y-6">

                    <?php if ($complaint['status'] === 'rejected'): ?>
                        <!-- สถานะยุติเรื่อง: แสดงเป็นแจ้งเตือนทางการ แทน stepper -->
                        <div class="bg-rose-50 border border-rose-200 rounded-lg p-4 flex items-start gap-3">
                            <span class="text-rose-500 text-lg leading-none">✕</span>
                            <p class="text-sm text-rose-800">
                                เรื่องนี้ได้รับการพิจารณาแล้ว และ<span class="font-semibold">ยุติการดำเนินการ</span> เนื่องจากข้อมูลไม่เพียงพอ หรือไม่อยู่ในขอบเขตที่ดำเนินการได้ โปรดดูรายละเอียดในส่วนคำตอบกลับด้านล่าง (ถ้ามี)
                            </p>
                        </div>
                    <?php else: ?>
                        <!-- ░░ Stepper: รับเรื่อง → ตรวจสอบ → เสร็จสิ้น ░░ -->
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 tracking-[0.2em] uppercase mb-4">ความคืบหน้าการดำเนินการ</p>
                            <div class="flex items-center">
                                <?php
                                $steps = ['รับเรื่องแล้ว', 'กำลังตรวจสอบ', 'ดำเนินการเสร็จสิ้น'];
                                foreach ($steps as $i => $label):
                                    $is_done    = $i < $step_index;
                                    $is_current = $i === $step_index;
                                    $dot_class  = $is_done ? 'done' : ($is_current ? 'current' : '');
                                ?>
                                    <div class="flex items-center <?php echo $i < 2 ? 'flex-1' : ''; ?>">
                                        <div class="flex flex-col items-center shrink-0" style="width:84px;">
                                            <div class="step-dot <?php echo $dot_class; ?> w-8 h-8 rounded-full flex items-center justify-center text-xs font-extrabold">
                                                <?php echo $is_done ? '✓' : ($i + 1); ?>
                                            </div>
                                            <span class="text-[10px] sm:text-[11px] font-semibold mt-1.5 text-center <?php echo $is_current ? 'text-[#0F1B3A]' : 'text-slate-400'; ?>"><?php echo $label; ?></span>
                                        </div>
                                        <?php if ($i < 2): ?>
                                            <div class="step-line <?php echo $i < $step_index ? 'filled' : ''; ?> flex-1 h-[2px] -mt-4"></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- รายละเอียดที่แจ้ง -->
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 tracking-[0.2em] uppercase mb-2">รายละเอียดเรื่องที่แจ้ง</p>
                        <p class="text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg p-4 leading-relaxed whitespace-pre-line"><?php echo nl2br(htmlspecialchars($complaint['details'])); ?></p>
                    </div>

                    <!-- คำตอบกลับ -->
                    <?php if ($complaint['admin_reply']): ?>
                        <div class="bg-amber-50 border border-amber-200 border-l-4 border-l-amber-500 rounded-lg p-4">
                            <p class="text-[11px] font-bold text-amber-800 tracking-[0.15em] uppercase mb-1.5">💬 บันทึกข้อความตอบกลับ</p>
                            <p class="text-sm text-slate-800 leading-relaxed whitespace-pre-line"><?php echo nl2br(htmlspecialchars($complaint['admin_reply'])); ?></p>
                            <p class="text-[11px] text-amber-700/70 mt-3 pt-2 border-t border-amber-200/70">— งานปกครอง ฝ่ายกิจการนักเรียนนักศึกษา วิทยาลัยอาชีวศึกษามหาสารคาม</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Footer เอกสาร -->
                <div class="px-5 sm:px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-between items-center text-[11px] text-slate-400">
                    <span>ระบบรับแจ้งเรื่องร้องเรียนอิเล็กทรอนิกส์ MVC</span>
                    <span>วันที่แจ้งเรื่อง: <?php echo date('d/m/Y H:i', strtotime($complaint['created_at'])); ?> น.</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ░░ Footer สถาบัน ░░ -->
    <footer class="max-w-2xl mx-auto px-4 mt-4">
        <div class="letterhead-rule mb-4"></div>
        <p class="text-center text-[11px] text-slate-400 leading-relaxed">
            © วิทยาลัยอาชีวศึกษามหาสารคาม (MVC) — Mahasarakham Vocational College<br>
            ระบบไม่บันทึกชื่อ ไอพีแอดเดรส หรือข้อมูลส่วนตัวของผู้แจ้งเรื่อง
        </p>
    </footer>

</body>
</html>