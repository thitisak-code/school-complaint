<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$type = $_GET['type'] ?? 'complaints';
$is_suggestions = $type === 'suggestions';
$category = 'ข้อเสนอแนะเพื่อพัฒนาวิทยาลัย';
$title = $is_suggestions ? 'รายงานข้อเสนอแนะเพื่อพัฒนาวิทยาลัย' : 'รายงานเรื่องร้องเรียน';
$filter = $is_suggestions ? 'category = :category' : 'category <> :category';

$stmt = $pdo->prepare("SELECT ticket_code, category, details, status, admin_reply, created_at FROM complaints WHERE {$filter} ORDER BY created_at ASC, id ASC");
$stmt->execute(['category' => $category]);
$records = $stmt->fetchAll();

$status_labels = [
    'pending' => 'รอดำเนินการ',
    'in_progress' => $is_suggestions ? 'กำลังพิจารณา' : 'กำลังตรวจสอบ',
    'resolved' => $is_suggestions ? 'นำไปดำเนินการแล้ว' : 'แก้ไขแล้ว',
    'rejected' => $is_suggestions ? 'ยังไม่ได้นำไปใช้' : 'ยุติเรื่อง/ยกเลิก',
];
$printed_at = date('d/m/Y H:i');

// จำกัด records ต่อหน้า
$records_per_page = 15;
$pages = array_chunk($records, $records_per_page);
$total_pages = count($pages);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link rel="icon" href="assets/Logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&family=Trirong:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { color: #172033; font-family: 'Sarabun', sans-serif; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #e8ebf0; }
        .screen-actions { max-width: 1100px; margin: 18px auto; display: flex; justify-content: flex-end; gap: 8px; }
        .screen-actions button, .screen-actions a { border: 0; border-radius: 5px; padding: 9px 15px; font: 600 13px 'Sarabun', sans-serif; cursor: pointer; text-decoration: none; }
        .print-button { color: #fff; background: #0f1b3a; }
        .back-button { color: #172033; background: #fff; border: 1px solid #cbd2dd !important; }
        .paper { max-width: 1100px; margin: 0 auto 28px; padding: 18mm 15mm 16mm; background: #fff; box-shadow: 0 2px 12px rgba(15, 27, 58, .14); page-break-after: always; display: flex; flex-direction: column; }
        .paper:last-child { margin-bottom: 0; }
        .report-header { display: flex; align-items: center; gap: 14px; border-bottom: 3px double #172033; padding-bottom: 11px; }
        .report-header img { width: 58px; height: 58px; object-fit: contain; }
        .report-header h1 { margin: 0; font: 700 20px 'Trirong', serif; }
        .report-header p { margin: 3px 0 0; font-size: 12px; }
        .report-meta { display: flex; justify-content: space-between; gap: 12px; margin: 14px 0 9px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 11px; margin-bottom: 9px; }
        th, td { border: 1px solid #687386; padding: 7px 6px; vertical-align: top; overflow-wrap: anywhere; }
        th { background: #e9edf3; text-align: center; font-weight: 700; }
        tbody tr { page-break-inside: avoid; break-inside: avoid; }
        .col-number { width: 10%; text-align: center; }
        .col-ticket { width: 12%; }
        .col-category { width: 16%; }
        .col-details { width: 31%; }
        .col-status { width: 14%; }
        .col-date { width: 12%; }
        .col-reply { width: 20%; }
        .empty { padding: 25px; text-align: center; border: 1px solid #687386; font-size: 13px; }
        .report-footer { margin-top: 12px; display: flex; justify-content: space-between; font-size: 11px; color: #4c586b; }
        .signature { margin-top: 8px; font-size: 11px; display: flex; justify-content: space-between; align-items: center; }
        .page-info { font-size: 11px; color: #4c586b; margin-top: 8px; }
        @page { size: A4 portrait; margin: 12mm 12mm 15mm; }
        @media print {
            html { width: 210mm; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            body { width: 210mm; background: #fff; }
            .screen-actions { display: none; }
            .paper { width: 186mm; max-width: none; margin: 0; padding: 18mm 15mm; box-shadow: none; page-break-after: always; }
            .paper:last-child { page-break-after: avoid; }
            .report-header { break-inside: avoid; }
            .report-footer { break-inside: avoid; }
            .signature { break-inside: avoid; }
            table { page-break-inside: avoid; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>
    <div class="screen-actions">
        <a class="back-button" href="<?php echo $is_suggestions ? 'suggestions.php' : 'admin.php'; ?>">← กลับหน้าจัดการ</a>
        <button class="print-button" type="button" onclick="window.print()">🖨️ พิมพ์เอกสาร</button>
    </div>

    <?php if (!$records): ?>
        <main class="paper">
            <header class="report-header">
                <img src="assets/Logo.png" alt="ตราวิทยาลัย">
                <div>
                    <h1><?php echo htmlspecialchars($title); ?></h1>
                    <p>วิทยาลัยอาชีวศึกษามหาสารคาม | เอกสารสำหรับใช้ประกอบการบริหารและพัฒนาวิทยาลัย</p>
                </div>
            </header>
            <div class="empty">ไม่พบข้อมูลสำหรับจัดทำรายงาน</div>
            <div class="report-footer">
                <span>ระบบรับเรื่องร้องเรียนและข้อเสนอแนะ</span>
                <span>หน้า 1/1</span>
            </div>
            <div class="signature">
                <span>ลงชื่อ ......................</span>
                <span>ผู้จัดทำรายงาน</span>
            </div>
        </main>
    <?php else: ?>
        <?php foreach ($pages as $page_num => $page_records): ?>
            <main class="paper">
                <header class="report-header">
                    <img src="assets/Logo.png" alt="ตราวิทยาลัย">
                    <div>
                        <h1><?php echo htmlspecialchars($title); ?></h1>
                        <p>วิทยาลัยอาชีวศึกษามหาสารคาม | เอกสารสำหรับใช้ประกอบการบริหารและพัฒนาวิทยาลัย</p>
                    </div>
                </header>

                <div class="report-meta">
                    <span>ประเภทข้อมูล: <?php echo $is_suggestions ? 'ข้อเสนอแนะ' : 'เรื่องร้องเรียน'; ?></span>
                    <span>พิมพ์เมื่อ: <?php echo $printed_at; ?> น. | รวม <?php echo count($records); ?> รายการ</span>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th class="col-number">ลำดับ</th>
                            <th class="col-ticket">รหัสติดตาม</th>
                            <th class="col-category">หมวดหมู่</th>
                            <th class="col-details">รายละเอียด</th>
                            <th class="col-status">สถานะ</th>
                            <th class="col-date">วันที่แจ้ง</th>
                            <th class="col-reply">ผลการดำเนินงาน / ข้อเสนอแนะตอบกลับ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($page_records as $index => $record): ?>
                            <tr>
                                <td class="col-number"><?php echo ($page_num * $records_per_page) + $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($record['ticket_code']); ?></td>
                                <td><?php echo htmlspecialchars($record['category']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($record['details'])); ?></td>
                                <td><?php echo htmlspecialchars($status_labels[$record['status']] ?? $record['status']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($record['created_at'])); ?> น.</td>
                                <td><?php echo nl2br(htmlspecialchars($record['admin_reply'] ?? '-')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="report-footer">
                    <span>ระบบรับเรื่องร้องเรียนและข้อเสนอแนะ</span>
                    <span>หน้า <?php echo $page_num + 1; ?>/<?php echo $total_pages; ?></span>
                </div>
                <div class="signature">
                    <span>ลงชื่อ ......................</span>
                    <span>ผู้จัดทำรายงาน</span>
                </div>
            </main>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
