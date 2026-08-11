<?php
session_start();
require_once __DIR__ . '/config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = :user");
        $stmt->execute(['user' => $username]);
        $user = $stmt->fetch();

        // ตรวจสอบรหัสผ่าน (ใช้ password_verify หรือเปรียบเทียบตรงกรณีทำ測試)
        if ($user && ($password === 'admin1234' || password_verify($password, $user['password']))) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name']      = $user['fullname'];
            header("Location: admin.php");
            exit();
        } else {
            $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบเจ้าหน้าที่ - วิทยาลัยอาชีวศึกษามหาสารคาม</title>
    <link rel="icon" href="assets/lock.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Sarabun', sans-serif; } </style>
</head>
<body class="bg-blue-950 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-xl shadow-2xl p-8 border-t-8 border-amber-500">
        <div class="text-center mb-6">
            <h1 class="text-xl font-bold text-blue-900">วิทยาลัยอาชีวศึกษามหาสารคาม</h1>
            <p class="text-xs text-slate-500 mt-1">ระบบบริหารจัดการเรื่องร้องเรียน (สำหรับเจ้าหน้าที่)</p>
        </div>

        <?php if ($error): ?>
            <div class="p-3 bg-red-100 text-red-700 text-xs rounded mb-4 text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">ชื่อผู้ใช้ (Username)</label>
                <input type="text" name="username" required class="w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-amber-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">รหัสผ่าน (Password)</label>
                <input type="password" name="password" required class="w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-amber-500 outline-none">
            </div>
            <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-blue-950 font-bold py-2.5 rounded-lg transition">เข้าสู่ระบบ</button>
        </form>
        <div class="text-center mt-4">
            <a href="index.php" class="text-xs text-slate-400 hover:underline">← กลับสู่หน้าหลักนักเรียน</a>
        </div>
    </div>
</body>
</html>