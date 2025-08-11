<?php
session_start();
include 'connect.php';

// Kiểm tra đăng nhập admin (nếu cần)
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = $_POST['appointment_id'];
    $diagnosis = $_POST['diagnosis'];
    $prescription = $_POST['prescription'];
    $notes = $_POST['notes'];

    $stmt = $conn->prepare("INSERT INTO treatment_results (appointment_id, diagnosis, prescription, notes) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $appointment_id, $diagnosis, $prescription, $notes);
    $stmt->execute();

    echo "<script>alert('Đã thêm kết quả điều trị!'); window.location.href='reports.php';</script>";
    exit;
}

// Lấy danh sách lịch hẹn để chọn
$result = $conn->query("
    SELECT a.appointment_id, u.full_name AS patient_name, d.full_name AS doctor_name, a.appointment_date
    FROM appointments a
    JOIN users u ON a.patient_id = u.user_id
    JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.status = 'confirmed'
    ORDER BY a.appointment_date DESC
");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>Thêm Kết Quả Điều Trị - Bệnh viện ABC</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        /* Reset cơ bản */
        * {
            margin: 0; 
            padding: 0; 
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to bottom right, #f0f8ff, #e6f7ff);
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.5;
        }
        header {
            background: #005bbb;
            padding: 16px 32px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .menu {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }
        .menu a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            transition: background 0.3s;
            white-space: nowrap;
        }
        .menu a.active, .menu a:hover {
            background: rgba(255, 9, 9, 0.2);
        }
        .login-btn a {
            background: #ffd700;
            color: #000;
            padding: 8px 16px;
            border-radius: 999px;
            font-weight: bold;
            text-decoration: none;
            transition: background 0.3s;
            white-space: nowrap;
        }
        .login-btn a:hover {
            background: #e6c200;
        }
        main.container {
            background: #fff;
            padding: 40px 30px;
            margin: 30px auto;
            border-radius: 16px;
            box-shadow: 0 0 24px rgba(0,0,0,0.1);
            max-width: 800px;
            flex-grow: 1;
            width: 95%;
        }
        h2 {
            color: #005bbb;
            margin-bottom: 20px;
            font-size: 28px;
            text-align: center;
        }
        form label {
            display: block;
            font-weight: 600;
            margin-top: 20px;
            margin-bottom: 6px;
            color: #333;
        }
        form select, form textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            resize: vertical;
        }
        form select:focus, form textarea:focus {
            border-color: #005bbb;
            outline: none;
        }
        form button {
            margin-top: 30px;
            padding: 14px 32px;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            background: #007bff;
            color: white;
            transition: background 0.3s ease;
            display: block;
            width: 100%;
            max-width: 200px;
            margin-left: auto;
            margin-right: auto;
        }
        form button:hover {
            background: #0056b3;
        }
        footer {
            background: #005bbb;
            color: white;
            padding: 40px 32px;
            margin-top: 40px;
            flex-shrink: 0;
        }
        .footer-grid {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 40px;
        }
        .footer-grid > div {
            flex: 1 1 250px;
        }
        .footer-grid h4 {
            color: #ffd700;
            margin-bottom: 12px;
            font-size: 18px;
        }
        .footer-grid p, .footer-grid a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            line-height: 1.6;
        }
        .footer-grid a:hover {
            text-decoration: underline;
        }
        .copyright {
            margin-top: 20px;
            text-align: center;
            color: #ffe066;
            font-size: 13px;
        }
        @media (max-width: 768px) {
            main.container {
                padding: 30px 20px;
            }
            .footer-grid {
                flex-direction: column;
                gap: 24px;
            }
            header {
                justify-content: center;
                gap: 16px;
            }
            .menu {
                gap: 12px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<header>
    <div><strong></strong></div>
    <nav class="menu">
        <a href="admin.php">Trang Chủ</a>
        <a href="user_mng.php">QL Người Dùng</a>
        <a href="doctors_mng.php">QL Bác Sĩ</a>
        <a href="manage_specialties.php">QL Chuyên Khoa</a>
        <a href="appointment_mng.php">QL Lịch Khám</a>
        <a href="manage_payments.php">QL Thanh Toán</a>
        <a href="reports.php" class="active">Báo Cáo</a>
    </nav>
    <a href="#" onclick="confirmLogout(event)">Đăng xuất</a>

</header>

<main class="container">
    <h2>➕ Thêm Kết Quả Điều Trị</h2>
    <form method="post" autocomplete="off">
        <label for="appointment_id">Lịch hẹn:</label>
        <select name="appointment_id" id="appointment_id" required>
            <option value="">-- Chọn lịch hẹn --</option>
            <?php while ($row = $result->fetch_assoc()): ?>
                <option value="<?= $row['appointment_id'] ?>">
                    <?= $row['appointment_id'] ?> - <?= htmlspecialchars($row['patient_name']) ?> với <?= htmlspecialchars($row['doctor_name']) ?> (<?= $row['appointment_date'] ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label for="diagnosis">Chẩn đoán:</label>
        <textarea name="diagnosis" id="diagnosis" rows="4" required></textarea>

        <label for="prescription">Toa thuốc:</label>
        <textarea name="prescription" id="prescription" rows="3"></textarea>

        <label for="notes">Ghi chú:</label>
        <textarea name="notes" id="notes" rows="3"></textarea>

        <button type="submit">Lưu kết quả</button>
    </form>
</main>

<footer>
    <div class="footer-grid">
        <div>
            <h4>Về Bệnh viện ABC</h4>
            <p>Chúng tôi cam kết cung cấp dịch vụ y tế chất lượng cao, tận tâm vì sức khỏe cộng đồng.</p>
        </div>
        <div>
            <h4>Thông tin liên hệ</h4>
            <p>Địa chỉ: 123 Đường Sức Khỏe, Quận 1, TP.HCM</p>
            <p>Email: contact@benhvienabc.vn</p>
            <p>Điện thoại: 028 1234 5678</p>
        </div>
        <div>
            <h4>Liên kết nhanh</h4>
            <p><a href="admin.php">Trang Chủ</a></p>
            <p><a href="user_mng.php">Quản lý Người Dùng</a></p>
            <p><a href="doctors_mng.php">Quản lý Bác Sĩ</a></p>
            <p><a href="manage_payments.php">Quản lý Thanh Toán</a></p>
        </div>
    </div>
    <div class="copyright">&copy; 2025 Bệnh viện Mê Gái Xinh. Bản quyền thuộc về chúng tôi.</div>
</footer>
<script>
function confirmLogout(event) {
    event.preventDefault(); // Ngăn chuyển hướng ngay lập tức

    const confirmResult = confirm("Bạn có chắc chắn muốn đăng xuất không?");
    if (confirmResult) {
        // Nếu người dùng chọn "OK" → chuyển đến trang logout
        window.location.href = "logout.php";
    }
    // Nếu chọn "Cancel", không làm gì cả
}
</script>

</body>
</html>
