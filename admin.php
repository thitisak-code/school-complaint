<?php
session_start();
require_once __DIR__ . '/config/db.php';

// 1. ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$msg = '';
$error = '';

// 2. ประมวลผลการอัปเดตสถานะการร้องเรียน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update_complaint'])) {
    $id          = intval($_POST['id']);
    $status      = $_POST['status'];
    $admin_reply = trim($_POST['admin_reply']);

    $stmt = $pdo->prepare("UPDATE complaints SET status = :status, admin_reply = :reply WHERE id = :id");
    if ($stmt->execute(['status' => $status, 'reply' => $admin_reply, 'id' => $id])) {
        $msg = 'อัปเดตสถานะเรื่องร้องเรียนเรียบร้อยแล้ว';
    }
}

// 3. ประมวลผลการลบเรื่องร้องเรียน
if (isset($_GET['delete_complaint'])) {
    $del_id = intval($_GET['delete_complaint']);
    
    $stmt_file = $pdo->prepare("SELECT image_path FROM complaints WHERE id = :id");
    $stmt_file->execute(['id' => $del_id]);
    $file_data = $stmt_file->fetch();
    if ($file_data && !empty($file_data['image_path']) && file_exists($file_data['image_path'])) {
        @unlink($file_data['image_path']);
    }

    $stmt = $pdo->prepare("DELETE FROM complaints WHERE id = :id");
    $stmt->execute(['id' => $del_id]);
    $msg = 'ลบเรื่องร้องเรียนเรียบร้อยแล้ว';
}

// 4. ประมวลผลการจัดการบัญชีผู้ดูแล (Admin Management)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_manage_admin'])) {
    $sub_action = $_POST['sub_action'];

    if ($sub_action === 'add') {
        $username = trim($_POST['username']);
        $fullname = trim($_POST['fullname']);
        $password = trim($_POST['password']);

        if (!empty($username) && !empty($fullname) && !empty($password)) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            try {
                $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, fullname) VALUES (:u, :p, :f)");
                $stmt->execute(['u' => $username, 'p' => $hashed, 'f' => $fullname]);
                $msg = 'เพิ่มผู้ดูแลระบบใหม่สำเร็จ';
            } catch (PDOException $e) {
                $error = 'ชื่อผู้ใช้นี้มีในระบบแล้ว';
            }
        }
    } elseif ($sub_action === 'edit') {
        $id       = intval($_POST['admin_id']);
        $fullname = trim($_POST['fullname']);
        $password = trim($_POST['password']);

        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt   = $pdo->prepare("UPDATE admin_users SET fullname = :f, password = :p WHERE id = :id");
            $stmt->execute(['f' => $fullname, 'p' => $hashed, 'id' => $id]);
        } else {
            $stmt   = $pdo->prepare("UPDATE admin_users SET fullname = :f WHERE id = :id");
            $stmt->execute(['f' => $fullname, 'id' => $id]);
        }
        $msg = 'อัปเดตข้อมูลผู้ดูแลระบบสำเร็จ';
    }
}

// 5. ลบบัญชีผู้ดูแล
if (isset($_GET['delete_admin'])) {
    $del_admin_id = intval($_GET['delete_admin']);
    $count_admin = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
    if ($count_admin > 1) {
        $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = :id");
        $stmt->execute(['id' => $del_admin_id]);
        $msg = 'ลบบัญชีผู้ดูแลเรียบร้อยแล้ว';
    } else {
        $error = 'ไม่สามารถลบได้ เนื่องจากต้องมีผู้ดูแลในระบบอย่างน้อย 1 คน';
    }
}

// 6. ดึงข้อมูลสถิติสำหรับ Dashboard & แจ้งเตือนเรื่องใหม่
$stat_all         = $pdo->query("SELECT COUNT(*) FROM complaints")->fetchColumn();
$stat_pending     = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'pending'")->fetchColumn(); // เรื่องร้องเรียนใหม่/ค้างดำเนินการ
$stat_in_progress = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'in_progress'")->fetchColumn();
$stat_resolved    = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'resolved'")->fetchColumn();
$stat_rejected    = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'rejected'")->fetchColumn();

// 7. ดึงรายการเรื่องร้องเรียนใหม่ (status = pending)
$stmt_new = $pdo->query("SELECT * FROM complaints WHERE status = 'pending' ORDER BY id DESC");
$new_complaints = $stmt_new->fetchAll();

// 8. ดึงรายการร้องเรียนค้างดำเนินการเกิน 3 วัน (Overdue Alert)
$overdue_threshold = date('Y-m-d H:i:s', strtotime('-3 days'));
$stmt_overdue = $pdo->prepare("SELECT * FROM complaints WHERE status = 'pending' AND created_at <= :thresh ORDER BY created_at ASC");
$stmt_overdue->execute(['thresh' => $overdue_threshold]);
$overdue_list = $stmt_overdue->fetchAll();

// 9. ดึงเรื่องร้องเรียนทั้งหมด
$stmt_all = $pdo->query("SELECT * FROM complaints ORDER BY id DESC");
$complaints = $stmt_all->fetchAll();

// 10. ดึงรายชื่อ Admin ทั้งหมด
$admin_list = $pdo->query("SELECT id, username, fullname FROM admin_users ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบหลังบ้าน - วิทยาลัยอาชีวศึกษามหาสารคาม</title>
    <link rel="icon" href="assets/Logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Sarabun', sans-serif; } </style>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col md:flex-row">

    <!-- Overlay สำหรับมือถือยามเปิด Slide Menu -->
    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 z-40 hidden md:hidden"></div>

    <!-- 🚪 SIDEBAR SLIDE MENU -->
    <aside id="sidebar" class="fixed md:static inset-y-0 left-0 w-64 bg-blue-950 text-white z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col justify-between shadow-xl">
        <div>
            <!-- Header Sidebar -->
            <div class="p-4 border-b border-blue-900 flex items-center space-x-3 bg-blue-900/50">
                <img src="assets/Logo.png" alt="MVC Logo" class="h-10 w-auto bg-white p-1 rounded-full shadow">
                <div>
                    <h2 class="text-sm font-bold leading-tight">งานปกครอง MVC</h2>
                    <p class="text-[10px] text-amber-400">วิทยาลัยอาชีวศึกษามหาสารคาม</p>
                </div>
            </div>

            <!-- Profile Info -->
            <div class="p-4 bg-blue-900/20 border-b border-blue-900 text-xs">
                <p class="text-slate-400">ผู้ใช้งานปัจจุบัน:</p>
                <p class="font-bold text-amber-300 truncate">👤 <?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
            </div>

            <!-- Navigation Links -->
            <nav class="p-3 space-y-1.5 text-xs font-semibold">
                <button onclick="switchTab('dashboard')" id="btn-dashboard" class="nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg bg-amber-500 text-blue-950 font-bold transition">
                    <span class="flex items-center gap-2">📊 ภาพรวมระบบ</span>
                </button>

                <button onclick="switchTab('complaints')" id="btn-complaints" class="nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-blue-900 text-slate-200 transition">
                    <span class="flex items-center gap-2">📥 เรื่องร้องเรียนทั้งหมด</span>
                    <?php if ($stat_pending > 0): ?>
                        <span class="bg-red-500 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full animate-pulse"><?php echo $stat_pending; ?></span>
                    <?php endif; ?>
                </button>

                <button onclick="switchTab('overdue')" id="btn-overdue" class="nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-blue-900 text-slate-200 transition">
                    <span class="flex items-center gap-2">⚠️ ค้างเกินกำหนด (SLA)</span>
                    <?php if (count($overdue_list) > 0): ?>
                        <span class="bg-amber-500 text-blue-950 text-[10px] font-extrabold px-2 py-0.5 rounded-full"><?php echo count($overdue_list); ?></span>
                    <?php endif; ?>
                </button>

                <button onclick="switchTab('admins')" id="btn-admins" class="nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-blue-900 text-slate-200 transition">
                    <span class="flex items-center gap-2">👤 จัดการผู้ดูแลระบบ</span>
                </button>
            </nav>
        </div>

        <!-- Sidebar Footer Actions -->
        <div class="p-3 border-t border-blue-900 space-y-1">
            <a href="index.php" target="_blank" class="block w-full text-center bg-blue-900 hover:bg-blue-800 text-slate-200 text-xs py-2 rounded-lg transition">🌐 เปิดหน้าเว็บนักเรียน</a>
            <a href="logout.php" class="block w-full text-center bg-red-600 hover:bg-red-700 text-white font-bold text-xs py-2 rounded-lg transition">🚪 ออกจากระบบ</a>
        </div>
    </aside>

    <!-- 📱 MAIN CONTENT AREA -->
    <div class="flex-1 flex flex-col min-w-0">
        
        <!-- Mobile Topbar -->
        <header class="bg-blue-950 text-white p-4 flex items-center justify-between md:hidden shadow-md">
            <button onclick="toggleSidebar()" class="text-amber-400 text-xl font-bold focus:outline-none">☰</button>
            <h1 class="text-sm font-bold">แผงควบคุมงานปกครอง (MVC)</h1>
            <?php if ($stat_pending > 0): ?>
                <span class="bg-red-500 text-white text-[10px] font-extrabold px-2 py-1 rounded-full">🔔 <?php echo $stat_pending; ?></span>
            <?php else: ?>
                <div></div>
            <?php endif; ?>
        </header>

        <!-- Dynamic Content Body -->
        <main class="p-4 sm:p-8 flex-1 overflow-y-auto space-y-6">

            <!-- System Alerts -->
            <?php if ($msg): ?>
                <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-800 rounded-lg shadow-sm text-sm font-semibold">
                    ✅ <?php echo $msg; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-800 rounded-lg shadow-sm text-sm font-semibold">
                    ❌ <?php echo $error; ?>
                </div>
            <?php endif; ?>


            <!-- ---------------------------------------------------- -->
            <!-- 📊 TAB 1: DASHBOARD -->
            <!-- ---------------------------------------------------- -->
            <section id="tab-dashboard" class="tab-content space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">📊 ภาพรวมระบบ (Dashboard)</h2>
                    <p class="text-xs text-slate-500">สรุปสถิติเรื่องร้องเรียนและการดำเนินงานทั้งหมด</p>
                </div>

                <!-- 🔔 NEW COMPLAINTS ALERT BANNER -->
                <?php if ($stat_pending > 0): ?>
                    <div class="bg-gradient-to-r from-amber-500 to-amber-600 text-blue-950 p-4 rounded-xl shadow-md flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🔔</span>
                            <div>
                                <h3 class="font-bold text-sm">มีเรื่องร้องเรียนใหม่เข้ามารอการตรวจสอบ <?php echo $stat_pending; ?> รายการ!</h3>
                                <p class="text-xs text-blue-900">โปรดคลิกเข้าดูรายละเอียดเพื่อดำเนินการแก้ไขปัญหาโดยเร็ว</p>
                            </div>
                        </div>
                        <button onclick="switchTab('complaints')" class="bg-blue-950 hover:bg-blue-900 text-white font-bold text-xs px-4 py-2 rounded-lg transition">
                            ดูเรื่องร้องเรียนใหม่ →
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Stats Cards Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-center">
                        <span class="text-xs text-slate-500 font-semibold block">เรื่องทั้งหมด</span>
                        <span class="text-3xl font-extrabold text-slate-800"><?php echo $stat_all; ?></span>
                    </div>
                    <div class="bg-amber-50 p-4 rounded-xl border border-amber-200 shadow-sm text-center">
                        <span class="text-xs text-amber-700 font-semibold block">⏳ รอดำเนินการ</span>
                        <span class="text-3xl font-extrabold text-amber-600"><?php echo $stat_pending; ?></span>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-200 shadow-sm text-center">
                        <span class="text-xs text-blue-700 font-semibold block">🔄 กำลังตรวจสอบ</span>
                        <span class="text-3xl font-extrabold text-blue-600"><?php echo $stat_in_progress; ?></span>
                    </div>
                    <div class="bg-green-50 p-4 rounded-xl border border-green-200 shadow-sm text-center">
                        <span class="text-xs text-green-700 font-semibold block">✅ แก้ไขแล้ว</span>
                        <span class="text-3xl font-extrabold text-green-600"><?php echo $stat_resolved; ?></span>
                    </div>
                    <div class="bg-red-50 p-4 rounded-xl border border-red-200 shadow-sm text-center col-span-2 sm:col-span-1">
                        <span class="text-xs text-red-700 font-semibold block">❌ ยุติเรื่อง/ยกเลิก</span>
                        <span class="text-3xl font-extrabold text-red-600"><?php echo $stat_rejected; ?></span>
                    </div>
                </div>

                <!-- Recent New Complaints Preview Table -->
                <div class="bg-white rounded-xl shadow-md border border-slate-200 p-5">
                    <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center justify-between">
                        <span>🆕 เรื่องร้องเรียนสัปดาห์ล่าสุด (รอดำเนินการ)</span>
                        <button onclick="switchTab('complaints')" class="text-xs text-indigo-600 hover:underline">ดูทั้งหมด →</button>
                    </h3>
                    <?php if (count($new_complaints) === 0): ?>
                        <p class="text-center text-slate-400 text-xs py-6">ไม่มีเรื่องร้องเรียนค้างดำเนินการ 🎉</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach (array_slice($new_complaints, 0, 3) as $nc): ?>
                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 flex flex-wrap justify-between items-center text-xs gap-2">
                                    <div>
                                        <span class="font-mono font-bold text-indigo-600"><?php echo $nc['ticket_code']; ?></span>
                                        <span class="ml-2 font-semibold text-slate-700">[<?php echo htmlspecialchars($nc['category']); ?>]</span>
                                        <p class="text-slate-500 mt-1"><?php echo mb_strimwidth(htmlspecialchars($nc['details']), 0, 80, "..."); ?></p>
                                    </div>
                                    <button onclick="switchTab('complaints')" class="bg-amber-500 hover:bg-amber-600 text-blue-950 font-bold px-3 py-1.5 rounded transition">จัดการเรื่องนี้</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>


            <!-- ---------------------------------------------------- -->
            <!-- 📥 TAB 2: COMPLAINTS LIST -->
            <!-- ---------------------------------------------------- -->
            <section id="tab-complaints" class="tab-content hidden space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">📥 รายการเรื่องร้องเรียนทั้งหมด</h2>
                    <p class="text-xs text-slate-500">ตรวจสอบ ปรับเปลี่ยนสถานะ และพิมพ์ตอบกลับนักเรียน</p>
                </div>

                <div class="space-y-4">
                    <?php if (count($complaints) === 0): ?>
                        <div class="bg-white p-8 rounded-xl text-center text-slate-400 text-sm">ยังไม่มีเรื่องร้องเรียนในระบบ</div>
                    <?php endif; ?>

                    <?php foreach ($complaints as $item): 
                        $is_overdue = ($item['status'] === 'pending' && strtotime($item['created_at']) <= strtotime('-3 days'));
                        $is_new     = ($item['status'] === 'pending');
                    ?>
                        <div class="bg-white rounded-xl shadow-sm border p-5 <?php echo $is_new ? 'border-amber-400 ring-1 ring-amber-400/50' : 'border-slate-200'; ?>">
                            <div class="flex flex-wrap justify-between items-start gap-2 border-b pb-3 mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-base font-bold text-indigo-600"><?php echo $item['ticket_code']; ?></span>
                                    <span class="text-xs font-semibold bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full"><?php echo htmlspecialchars($item['category']); ?></span>
                                    <?php if ($is_new): ?>
                                        <span class="bg-amber-500 text-blue-950 text-[10px] font-extrabold px-2 py-0.5 rounded-full">🔔 เรื่องใหม่</span>
                                    <?php endif; ?>
                                    <?php if ($is_overdue): ?>
                                        <span class="bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">⚠️ ค้างเกิน 3 วัน</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-slate-400">วันที่แจ้ง: <?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?> น.</span>
                                    <a href="admin.php?delete_complaint=<?php echo $item['id']; ?>" onclick="return confirm('ยืนยันที่จะลบเรื่องร้องเรียนนี้หรือไม่?')" class="text-xs text-red-600 font-semibold hover:underline">🗑️ ลบ</a>
                                </div>
                            </div>

                            <p class="text-slate-800 text-sm mb-4 bg-slate-50 p-3 rounded-lg border border-slate-100"><?php echo nl2br(htmlspecialchars($item['details'])); ?></p>

                            <?php if ($item['image_path']): ?>
                                <div class="mb-4">
                                    <a href="<?php echo htmlspecialchars($item['image_path']); ?>" target="_blank" class="inline-flex items-center gap-1 text-xs text-blue-600 font-semibold underline hover:text-blue-800">
                                        🖼️ ดูรูปภาพประกอบที่แนบมา
                                    </a>
                                </div>
                            <?php endif; ?>

                            <!-- Update Status Form -->
                            <form action="admin.php" method="POST" class="bg-slate-50 p-4 rounded-lg border border-slate-200 space-y-3">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="action_update_complaint" value="1">
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">สถานะการดำเนินการ</label>
                                        <select name="status" class="w-full text-xs p-2.5 border rounded-lg bg-white font-semibold outline-none focus:ring-2 focus:ring-amber-500">
                                            <option value="pending" <?php if($item['status']=='pending') echo 'selected'; ?>>⏳ รอดำเนินการ</option>
                                            <option value="in_progress" <?php if($item['status']=='in_progress') echo 'selected'; ?>>🔄 กำลังตรวจสอบ</option>
                                            <option value="resolved" <?php if($item['status']=='resolved') echo 'selected'; ?>>✅ ดำเนินการแก้ไขแล้ว</option>
                                            <option value="rejected" <?php if($item['status']=='rejected') echo 'selected'; ?>>❌ ยุติเรื่อง/ยกเลิก</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">ข้อความตอบกลับนักเรียน/ผู้แจ้งเรื่อง</label>
                                        <input type="text" name="admin_reply" value="<?php echo htmlspecialchars($item['admin_reply'] ?? ''); ?>" placeholder="พิมพ์ผลการตรวจสอบหรือการดำเนินงาน..." class="w-full text-xs p-2.5 border rounded-lg bg-white outline-none focus:ring-2 focus:ring-amber-500">
                                    </div>
                                </div>
                                
                                <div class="text-right">
                                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-blue-950 font-bold text-xs px-4 py-2 rounded-lg transition shadow-sm">
                                        💾 บันทึกการเปลี่ยนแปลง
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>


            <!-- ---------------------------------------------------- -->
            <!-- ⚠️ TAB 3: OVERDUE SLA ALERTS -->
            <!-- ---------------------------------------------------- -->
            <section id="tab-overdue" class="tab-content hidden space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">⚠️ เรื่องค้างดำเนินการเกินกำหนด (เกิน 3 วัน)</h2>
                    <p class="text-xs text-slate-500">รายการเรื่องร้องเรียนสถานะ "รอดำเนินการ" ที่ยังไม่ได้เริ่มตรวจสอบเกินระยะเวลาที่กำหนด</p>
                </div>

                <?php if (count($overdue_list) === 0): ?>
                    <div class="bg-white p-8 rounded-xl text-center text-slate-500 text-sm">
                        🎉 ยอดเยี่ยม! ไม่มีเรื่องร้องเรียนที่ค้างเกินกำหนดในขณะนี้
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($overdue_list as $od): ?>
                            <div class="bg-red-50 border border-red-300 p-4 rounded-xl shadow-sm space-y-2">
                                <div class="flex justify-between items-center text-xs border-b border-red-200 pb-2">
                                    <span class="font-mono font-bold text-indigo-600 text-sm"><?php echo $od['ticket_code']; ?></span>
                                    <span class="text-red-700 font-bold">ค้างไว้ตั้งแต่วันที่: <?php echo date('d/m/Y H:i', strtotime($od['created_at'])); ?> น.</span>
                                </div>
                                <p class="text-slate-800 text-xs font-medium"><?php echo nl2br(htmlspecialchars($od['details'])); ?></p>
                                <div class="text-right pt-1">
                                    <button onclick="switchTab('complaints')" class="bg-red-600 hover:bg-red-700 text-white font-bold text-xs px-3 py-1.5 rounded transition">
                                        ไปที่หน้ารายการเพื่อจัดการ →
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>


            <!-- ---------------------------------------------------- -->
            <!-- 👤 TAB 4: ADMIN USERS MANAGEMENT -->
            <!-- ---------------------------------------------------- -->
            <section id="tab-admins" class="tab-content hidden space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">👤 จัดการบัญชีผู้ดูแลระบบ (Admin Users)</h2>
                    <p class="text-xs text-slate-500">เพิ่ม แก้ไข หรือลบบัญชีผู้ใช้งานระบบหลังบ้านสำหรับเจ้าหน้าที่</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Form Add Admin -->
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                        <h3 class="text-sm font-bold text-blue-950 mb-3">➕ เพิ่มผู้ดูแลระบบใหม่</h3>
                        <form action="admin.php" method="POST" class="space-y-3">
                            <input type="hidden" name="action_manage_admin" value="1">
                            <input type="hidden" name="sub_action" value="add">
                            
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">ชื่อผู้ใช้ (Username)</label>
                                <input type="text" name="username" required class="w-full text-xs p-2.5 border rounded-lg bg-white outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">ชื่อ-นามสกุล / ตำแหน่ง</label>
                                <input type="text" name="fullname" required class="w-full text-xs p-2.5 border rounded-lg bg-white outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">รหัสผ่าน (Password)</label>
                                <input type="password" name="password" required class="w-full text-xs p-2.5 border rounded-lg bg-white outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                            <button type="submit" class="w-full bg-blue-950 hover:bg-blue-900 text-white font-bold text-xs py-2.5 rounded-lg transition">
                                บันทึกผู้ดูแลใหม่
                            </button>
                        </form>
                    </div>

                    <!-- Admin List Table -->
                    <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
                        <h3 class="text-sm font-bold text-blue-950 mb-3">รายชื่อผู้ดูแลในระบบ</h3>
                        <table class="w-full text-left text-xs text-slate-600 border border-slate-200 rounded-lg overflow-hidden">
                            <thead class="bg-blue-950 text-white">
                                <tr>
                                    <th class="p-3">Username</th>
                                    <th class="p-3">ชื่อ-นามสกุล / เปลี่ยนรหัส</th>
                                    <th class="p-3 text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php foreach ($admin_list as $adm): ?>
                                    <tr>
                                        <td class="p-3 font-semibold text-slate-800"><?php echo htmlspecialchars($adm['username']); ?></td>
                                        <td class="p-3">
                                            <form action="admin.php" method="POST" class="flex flex-wrap gap-1 items-center">
                                                <input type="hidden" name="action_manage_admin" value="1">
                                                <input type="hidden" name="sub_action" value="edit">
                                                <input type="hidden" name="admin_id" value="<?php echo $adm['id']; ?>">
                                                <input type="text" name="fullname" value="<?php echo htmlspecialchars($adm['fullname']); ?>" class="p-1.5 border rounded text-xs bg-slate-50">
                                                <input type="password" name="password" placeholder="เปลี่ยนรหัสผ่าน" class="p-1.5 border rounded text-xs w-28 bg-slate-50">
                                                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-blue-950 font-bold px-2 py-1 rounded text-xs">บันทึก</button>
                                            </form>
                                        </td>
                                        <td class="p-3 text-center">
                                            <a href="admin.php?delete_admin=<?php echo $adm['id']; ?>" onclick="return confirm('ยืนยันที่จะลบบัญชีผู้ดูแลนี้หรือไม่?')" class="text-red-600 font-bold hover:underline">ลบ</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- 🔮 JAVASCRIPT FOR TAB SWITCHING & SIDEBAR TOGGLE -->
    <script>
        // ฟังก์ชันสลับการเปิด-ปิด Sidebar บนมือถือ
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // ฟังก์ชันสลับ Tab หน้าต่างการทำงาน
        function switchTab(tabName) {
            // ซ่อนทุก Tab Content
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });

            // เอาสไตล์ Active ออกจากปุ่มทั้งหมด
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.classList.remove('bg-amber-500', 'text-blue-950', 'font-bold');
                btn.classList.add('hover:bg-blue-900', 'text-slate-200');
            });

            // แสดง Tab ที่เลือก
            document.getElementById('tab-' + tabName).classList.remove('hidden');

            // เพิ่มสไตล์ Active ให้ปุ่มที่กด
            const activeBtn = document.getElementById('btn-' + tabName);
            if (activeBtn) {
                activeBtn.classList.add('bg-amber-500', 'text-blue-950', 'font-bold');
                activeBtn.classList.remove('hover:bg-blue-900', 'text-slate-200');
            }

            // ถ้าอยู่ในหน้าจอมือถือ ให้ปิด Sidebar อัตโนมัติหลังจากคลิกเลือก
            if (window.innerWidth < 768) {
                toggleSidebar();
            }
        }
    </script>
</body>
</html>