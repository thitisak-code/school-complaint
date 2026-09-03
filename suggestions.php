<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$suggestion_category = 'ข้อเสนอแนะเพื่อพัฒนาวิทยาลัย';
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update_suggestion'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'] ?? 'pending';
    $admin_reply = trim($_POST['admin_reply'] ?? '');
    $allowed_statuses = ['pending', 'in_progress', 'resolved', 'rejected'];

    if (in_array($status, $allowed_statuses, true)) {
        $stmt = $pdo->prepare("UPDATE complaints SET status = :status, admin_reply = :reply WHERE id = :id AND category = :category");
        $stmt->execute([
            'status' => $status,
            'reply' => $admin_reply,
            'id' => $id,
            'category' => $suggestion_category,
        ]);
        $msg = 'อัปเดตข้อเสนอแนะเรียบร้อยแล้ว';
    } else {
        $error = 'สถานะที่เลือกไม่ถูกต้อง';
    }
}

if (isset($_GET['delete_suggestion'])) {
    $del_id = intval($_GET['delete_suggestion']);
    $stmt_file = $pdo->prepare("SELECT image_path FROM complaints WHERE id = :id AND category = :category");
    $stmt_file->execute(['id' => $del_id, 'category' => $suggestion_category]);
    $file_data = $stmt_file->fetch();

    if ($file_data && !empty($file_data['image_path']) && file_exists($file_data['image_path'])) {
        @unlink($file_data['image_path']);
    }

    $stmt = $pdo->prepare("DELETE FROM complaints WHERE id = :id AND category = :category");
    $stmt->execute(['id' => $del_id, 'category' => $suggestion_category]);
    $msg = 'ลบข้อเสนอแนะเรียบร้อยแล้ว';
}

$stat_stmt = $pdo->prepare("SELECT status, COUNT(*) AS total FROM complaints WHERE category = :category GROUP BY status");
$stat_stmt->execute(['category' => $suggestion_category]);
$stats = array_fill_keys(['pending', 'in_progress', 'resolved', 'rejected'], 0);
foreach ($stat_stmt->fetchAll() as $row) {
    $stats[$row['status']] = (int) $row['total'];
}
$total_suggestions = array_sum($stats);

$list_stmt = $pdo->prepare("SELECT * FROM complaints WHERE category = :category ORDER BY id DESC");
$list_stmt->execute(['category' => $suggestion_category]);
$suggestions = $list_stmt->fetchAll();

$status_labels = [
    'pending' => ['รอดำเนินการ', 'bg-amber-100 text-amber-700'],
    'in_progress' => ['กำลังพิจารณา', 'bg-blue-100 text-blue-700'],
    'resolved' => ['นำไปดำเนินการแล้ว', 'bg-green-100 text-green-700'],
    'rejected' => ['ยังไม่ได้นำไปใช้', 'bg-slate-200 text-slate-700'],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อเสนอแนะพัฒนาวิทยาลัย - ระบบหลังบ้าน</title>
    <link rel="icon" href="assets/Logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .suggestion-card { animation: cardFadeUp 0.4s ease-out both; }
        @keyframes cardFadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-slate-100 min-h-screen">
    <header class="bg-blue-950 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-8 py-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <img src="assets/Logo.png" alt="MVC Logo" class="h-10 w-auto bg-white p-1 rounded-full">
                <div>
                    <h1 class="font-bold">ข้อเสนอแนะเพื่อพัฒนาวิทยาลัย</h1>
                    <p class="text-[11px] text-amber-300">วิทยาลัยอาชีวศึกษามหาสารคาม</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <a href="admin.php" class="bg-blue-900 hover:bg-blue-800 px-3 py-2 rounded-lg transition">← กลับแดชบอร์ด</a>
                <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-3 py-2 rounded-lg transition">ออกจากระบบ</a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto p-4 sm:p-8 space-y-6">
        <?php if ($msg): ?>
            <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-800 rounded-lg text-sm font-semibold"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-800 rounded-lg text-sm font-semibold"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <section>
            <h2 class="text-xl font-bold text-slate-800">💡 ศูนย์รวมข้อเสนอแนะ</h2>
            <p class="text-xs text-slate-500 mt-1">รวบรวมความคิดเห็นเพื่อใช้กำหนดแนวทางปรับปรุงวิทยาลัย ติดตามผล และสื่อสารผลการดำเนินงาน</p>
        </section>

        <div class="flex justify-end print:hidden">
            <a href="report.php?type=suggestions" target="_blank" class="bg-blue-950 hover:bg-blue-900 text-white font-bold text-xs px-4 py-2.5 rounded-lg transition">🖨️ พิมพ์รายงานข้อเสนอแนะ</a>
        </div>

        <section class="grid grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="col-span-2 lg:col-span-1 bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-center">
                <span class="block text-xs text-slate-500 font-semibold">ข้อเสนอแนะทั้งหมด</span>
                <span class="text-3xl font-extrabold text-slate-800"><?php echo $total_suggestions; ?></span>
            </div>
            <div class="bg-amber-50 p-4 rounded-xl border border-amber-200 shadow-sm text-center"><span class="block text-xs text-amber-700 font-semibold">รอดำเนินการ</span><span class="text-2xl font-extrabold text-amber-600"><?php echo $stats['pending']; ?></span></div>
            <div class="bg-blue-50 p-4 rounded-xl border border-blue-200 shadow-sm text-center"><span class="block text-xs text-blue-700 font-semibold">กำลังพิจารณา</span><span class="text-2xl font-extrabold text-blue-600"><?php echo $stats['in_progress']; ?></span></div>
            <div class="bg-green-50 p-4 rounded-xl border border-green-200 shadow-sm text-center"><span class="block text-xs text-green-700 font-semibold">นำไปดำเนินการแล้ว</span><span class="text-2xl font-extrabold text-green-600"><?php echo $stats['resolved']; ?></span></div>
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 shadow-sm text-center"><span class="block text-xs text-slate-600 font-semibold">ยังไม่ได้นำไปใช้</span><span class="text-2xl font-extrabold text-slate-600"><?php echo $stats['rejected']; ?></span></div>
        </section>

        <section class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-slate-600">กรองตามสถานะ:</span>
            <button type="button" data-status="all" class="filter-btn px-3 py-1.5 rounded-full text-xs font-bold bg-blue-950 text-white ring-2 ring-amber-500">ทั้งหมด</button>
            <?php foreach ($status_labels as $status => $label): ?>
                <button type="button" data-status="<?php echo $status; ?>" class="filter-btn px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200"><?php echo $label[0]; ?></button>
            <?php endforeach; ?>
            <span id="filter-count" class="text-xs text-slate-400 ml-auto"></span>
        </section>

        <?php if (!$suggestions): ?>
            <div class="bg-white p-10 rounded-xl border border-slate-200 text-center text-slate-400 text-sm">ยังไม่มีข้อเสนอแนะเพื่อพัฒนาวิทยาลัย</div>
        <?php else: ?>
            <section id="suggestions-grid" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <?php foreach ($suggestions as $index => $suggestion):
                    [$status_text, $status_class] = $status_labels[$suggestion['status']] ?? ['ไม่ทราบสถานะ', 'bg-slate-100 text-slate-700'];
                ?>
                    <article class="suggestion-card bg-white rounded-xl border border-slate-200 shadow-sm p-5" data-status="<?php echo htmlspecialchars($suggestion['status']); ?>" style="animation-delay: <?php echo min($index * 40, 300); ?>ms;">
                        <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                            <div>
                                <span class="font-mono font-bold text-indigo-600 text-sm"><?php echo htmlspecialchars($suggestion['ticket_code']); ?></span>
                                <p class="text-[11px] text-slate-400 mt-1">แจ้งเมื่อ <?php echo date('d/m/Y H:i', strtotime($suggestion['created_at'])); ?> น.</p>
                            </div>
                            <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-full <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </div>
                        <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line py-4"><?php echo htmlspecialchars($suggestion['details']); ?></p>
                        <?php if (!empty($suggestion['image_path'])): ?>
                            <a href="<?php echo htmlspecialchars($suggestion['image_path']); ?>" target="_blank" class="inline-block text-xs text-indigo-600 hover:underline mb-3">🖼️ เปิดรูปภาพประกอบ</a>
                        <?php endif; ?>
                        <form action="suggestions.php" method="POST" class="border-t border-slate-100 pt-3 space-y-2">
                            <input type="hidden" name="action_update_suggestion" value="1">
                            <input type="hidden" name="id" value="<?php echo $suggestion['id']; ?>">
                            <div class="flex flex-wrap gap-2">
                                <select name="status" class="flex-1 min-w-[170px] text-xs p-2 border rounded-lg bg-white focus:ring-2 focus:ring-amber-500 outline-none">
                                    <?php foreach ($status_labels as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo $suggestion['status'] === $value ? 'selected' : ''; ?>><?php echo $label[0]; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-blue-950 font-bold text-xs px-3 py-2 rounded-lg">บันทึกผล</button>
                                <a href="suggestions.php?delete_suggestion=<?php echo $suggestion['id']; ?>" onclick="return confirm('ยืนยันที่จะลบข้อเสนอแนะนี้หรือไม่?')" class="text-red-600 hover:underline text-xs font-semibold px-2 py-2">ลบ</a>
                            </div>
                            <input type="text" name="admin_reply" value="<?php echo htmlspecialchars($suggestion['admin_reply'] ?? ''); ?>" placeholder="บันทึกแนวทางหรือผลการนำไปพัฒนา..." class="w-full text-xs p-2 border rounded-lg bg-white focus:ring-2 focus:ring-amber-500 outline-none">
                        </form>
                    </article>
                <?php endforeach; ?>
            </section>
            <p id="filter-empty" class="hidden bg-white p-8 rounded-xl text-center text-slate-400 text-sm">ไม่พบข้อเสนอแนะตามสถานะที่เลือก</p>
        <?php endif; ?>
    </main>

    <script>
        const filterButtons = document.querySelectorAll('.filter-btn');
        const suggestionCards = document.querySelectorAll('.suggestion-card');
        const countLabel = document.getElementById('filter-count');
        const emptyMessage = document.getElementById('filter-empty');

        function filterSuggestions(status) {
            let visibleCount = 0;
            suggestionCards.forEach(card => {
                const visible = status === 'all' || card.dataset.status === status;
                card.classList.toggle('hidden', !visible);
                if (visible) visibleCount++;
            });
            if (countLabel) countLabel.textContent = `แสดง ${visibleCount} จาก ${suggestionCards.length} รายการ`;
            if (emptyMessage) emptyMessage.classList.toggle('hidden', visibleCount !== 0);
        }

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                filterButtons.forEach(item => item.classList.remove('bg-blue-950', 'text-white', 'ring-2', 'ring-amber-500'));
                button.classList.add('bg-blue-950', 'text-white', 'ring-2', 'ring-amber-500');
                filterSuggestions(button.dataset.status);
            });
        });
        filterSuggestions('all');
    </script>
</body>
</html>
