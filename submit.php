<?php
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category']);
    $details  = trim($_POST['details']);
    $image_path = null;

    if (empty($category) || empty($details)) {
        die("กรุณากรอกข้อมูลให้ครบถ้วน");
    }

    // 1. สุ่มสร้าง Ticket Code (เช่น TK-8A2F1B)
    $ticket_code = 'TK-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

    // 2. จัดการรูปภาพ (ถ้ามีการอัปโหลด)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $new_filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $upload_dir   = 'uploads/';

            // สร้างภาพใหม่เพื่อลบ Metadata (EXIF Data) เพื่อความปลอดภัยของผู้ใช้
            if ($ext === 'jpg' || $ext === 'jpeg') {
                $img = @imagecreatefromjpeg($file_tmp);
                if ($img) {
                    imagejpeg($img, $upload_dir . $new_filename, 85);
                    imagedestroy($img);
                    $image_path = $upload_dir . $new_filename;
                }
            } else {
                if (move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
                    $image_path = $upload_dir . $new_filename;
                }
            }
        }
    }

    // 3. บันทึกลง Database
    $stmt = $pdo->prepare("INSERT INTO complaints (ticket_code, category, details, image_path) VALUES (:ticket, :category, :details, :image)");
    $stmt->execute([
        'ticket'   => $ticket_code,
        'category' => $category,
        'details'  => $details,
        'image'    => $image_path
    ]);
} else {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ส่งเรื่องเรียบร้อยแล้ว</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Sarabun', sans-serif; } </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white p-8 rounded-xl shadow-lg border border-slate-200 text-center">
        <div class="text-green-500 text-5xl mb-3">✅</div>
        <h2 class="text-2xl font-bold text-slate-800 mb-2">ส่งเรื่องร้องเรียนสำเร็จ!</h2>
        <p class="text-slate-600 text-sm mb-6">กรุณาบันทึกหรือจดจำ <b>Ticket Code</b> ด้านล่างนี้ไว้ เพื่อใช้ติดตามสถานะการดำเนินการ</p>
        
        <div class="bg-slate-100 border-2 border-dashed border-indigo-400 p-4 rounded-lg mb-6">
            <span class="text-xs text-slate-500 block">รหัสติดตามเรื่องของคุณ</span>
            <span class="text-3xl font-extrabold text-indigo-600 tracking-wider"><?php echo $ticket_code; ?></span>
        </div>

        <a href="track.php?code=<?php echo $ticket_code; ?>" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition mb-3">
            ไปที่หน้าติดตามสถานะทันที
        </a>
        <a href="index.php" class="text-sm text-slate-500 hover:underline">กลับสู่หน้าหลัก</a>
    </div>
</body>
</html>