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
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดตามสถานะ - วิทยาลัยอาชีวศึกษามหาสารคาม</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Sarabun', sans-serif; } </style>
</head>
<body class="bg-slate-100 min-h-screen pb-10">

    <!-- Header สถาบัน -->
    <nav class="bg-blue-900 border-b-4 border-amber-500 text-white p-4 shadow-md">
        <div class="max-w-3xl mx-auto flex justify-between items-center">
            <div>
                <h1 class="text-lg font-bold">วิทยาลัยอาชีวศึกษามหาสารคาม</h1>
                <p class="text-xs text-amber-400">ระบบติดตามสถานะเรื่องร้องเรียน</p>
            </div>
            <a href="index.php" class="bg-amber-500 hover:bg-amber-600 text-blue-950 font-bold text-xs px-3 py-2 rounded transition">➕ แจ้งเรื่องใหม่</a>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto mt-8 p-6 bg-white rounded-xl shadow-md border border-slate-200">
        <h2 class="text-xl font-bold text-slate-800 mb-4 text-center">🔍 ติดตามสถานะการดำเนินการ</h2>

        <form action="track.php" method="GET" class="flex gap-2 mb-6">
            <input type="text" name="code" value="<?php echo htmlspecialchars($ticket_code); ?>" placeholder="กรอก Ticket Code (เช่น TK-8A2F1B)" required class="flex-1 p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none font-mono text-center text-lg uppercase">
            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-blue-950 font-bold px-6 py-3 rounded-lg transition">ค้นหา</button>
        </form>

        <?php if ($error): ?>
            <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded mb-4 text-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($complaint): ?>
            <div class="border border-slate-200 rounded-lg p-5 bg-slate-50 space-y-4">
                <div class="flex justify-between items-center border-b pb-3">
                    <div>
                        <span class="text-xs text-slate-500 block">Ticket Code</span>
                        <span class="font-mono text-lg font-bold text-indigo-600"><?php echo $complaint['ticket_code']; ?></span>
                    </div>
                    <div>
                        <?php 
                        $status_badge = [
                            'pending'     => '<span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-3 py-1 rounded-full">⏳ รอดำเนินการ</span>',
                            'in_progress' => '<span class="bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">🔄 กำลังตรวจสอบ</span>',
                            'resolved'    => '<span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">✅ ดำเนินการแก้ไขแล้ว</span>',
                            'rejected'    => '<span class="bg-red-100 text-red-800 text-xs font-semibold px-3 py-1 rounded-full">❌ ยุติเรื่อง/ข้อมูลไม่เพียงพอ</span>'
                        ];
                        echo $status_badge[$complaint['status']];
                        ?>
                    </div>
                </div>

                <div>
                    <span class="text-xs text-slate-500 block">หมวดหมู่</span>
                    <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($complaint['category']); ?></span>
                </div>

                <div>
                    <span class="text-xs text-slate-500 block">รายละเอียดเรื่องที่แจ้ง</span>
                    <p class="text-sm text-slate-700 bg-white p-3 rounded border border-slate-200 mt-1"><?php echo nl2br(htmlspecialchars($complaint['details'])); ?></p>
                </div>

                <?php if ($complaint['admin_reply']): ?>
                    <div class="bg-amber-50 border border-amber-200 p-4 rounded-lg">
                        <span class="text-xs font-bold text-amber-800 block mb-1">💬 การตอบกลับจากงานปกครอง / วิทยาลัย:</span>
                        <p class="text-sm text-slate-800"><?php echo nl2br(htmlspecialchars($complaint['admin_reply'])); ?></p>
                    </div>
                <?php endif; ?>

                <div class="text-right text-xs text-slate-400 border-t pt-2">
                    แจ้งเรื่องเมื่อ: <?php echo date('d/m/Y H:i', strtotime($complaint['created_at'])); ?> น.
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>