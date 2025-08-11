<?php
session_start();
include 'connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_page = basename($_SERVER['PHP_SELF']);

// Lấy kết quả điều trị
$sql = "
    SELECT r.*, a.appointment_date, a.appointment_time, d.full_name AS doctor_name
    FROM treatment_results r
    JOIN appointments a ON r.appointment_id = a.appointment_id
    JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.patient_id = ?
    ORDER BY r.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết Quả Điều Trị - Bệnh viện ABC</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #01eeff;
            color: #febc37;
            line-height: 1.6;
        }
        header {
            background-color: rgb(35, 255, 1);
            padding: 15px 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo img {
            height: 120px;
            border-radius: 20px;
        }
        /* Menu */
        .menu {
            list-style: none;
            display: flex;
            gap: 25px;
            flex: 1;
            justify-content: center;
        }
        .menu li a {
            color: #000000;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            padding: 10px 16px;
            border-radius: 6px;
            position: relative;
            transition: 0.3s ease;
        }
        .menu li a::after {
            content: "";
            position: absolute;
            height: 3px;
            width: 0;
            background: #000;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            transition: 0.3s ease;
        }
        /* Hover + Active */
        .menu li a:hover::after,
        .menu li a.active::after {
            width: 60%;
        }
        .menu li a:hover,
        .menu li a.active {
            background-color: rgba(255, 255, 255, 0.15);
        }
        /* Nút đăng xuất */
        .login-btn a {
            color: white;
            background-color: #8401ff;
            padding: 10px 16px;
            border-radius: 6px;
            font-weight: bold;
            text-decoration: none;
            transition: 0.3s ease;
        }
        .login-btn a:hover {
            background-color: #6a040f;
        }
        /* Nội dung chính */
        main.dashboard {
            max-width: 1100px;
            margin: 30px auto 50px;
            padding: 30px;
            background-color: #00c2d1cc;
            border-radius: 15px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
            color: #fffbe6;
        }
        main.dashboard h2 {
            font-size: 30px;
            margin-bottom: 20px;
            font-weight: 700;
            text-align: center;
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #00b8d4cc;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 16px rgba(0,0,0,0.3);
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #0a5e67;
            text-align: left;
            color: #fffbe6;
            vertical-align: top;
        }
        th {
            background-color: #00a1b5;
            font-weight: 600;
            font-size: 16px;
            text-shadow: 0 1px 1px rgba(0,0,0,0.5);
        }
        tr:hover {
            background-color: #0091a1cc;
            cursor: default;
        }
        /* Footer */
        footer {
            background-color: #a41215;
            color: #febc37;
            padding: 40px 60px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 30px;
            box-shadow: inset 0 0 15px rgba(0,0,0,0.3);
        }
        footer .footer-column {
            flex: 1 1 220px;
            min-width: 220px;
        }
        footer .footer-column h3 {
            color: #febc37;
            margin-bottom: 18px;
            font-weight: 700;
            font-size: 18px;
            border-bottom: 2px solid #febc37;
            padding-bottom: 6px;
        }
        footer .footer-column p,
        footer .footer-column a,
        footer .footer-column span {
            color: white;
            font-weight: 400;
            line-height: 1.6;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }
        footer .footer-column a:hover {
            color: #febc37;
            text-decoration: underline;
        }
        footer .footer-bottom {
            width: 100%;
            text-align: center;
            color: white;
            font-weight: 400;
            font-size: 13px;
            margin-top: 35px;
            border-top: 1px solid rgba(255,188,55,0.3);
            padding-top: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <img src="imgcss/icon.png" alt="Logo Bệnh viện ABC">
    </div>
    <ul class="menu">
        <li><a href="patient.php" class="<?= $current_page == 'patient.php' ? 'active' : '' ?>">Trang Chủ</a></li>
        <li><a href="appointment.php" class="<?= $current_page == 'appointment.php' ? 'active' : '' ?>">Đặt Lịch</a></li>
        <li><a href="appointment_history.php" class="<?= $current_page == 'appointment_history.php' ? 'active' : '' ?>">Lịch Sử</a></li>
        <li><a href="payment.php" class="<?= $current_page == 'payment.php' ? 'active' : '' ?>">Thanh Toán</a></li>
        <li><a href="treatment_results.php" class="<?= $current_page == 'treatment_results.php' ? 'active' : '' ?>">Kết Quả</a></li>
        <li><a href="profile.php" class="<?= $current_page == 'profile.php' ? 'active' : '' ?>">Hồ Sơ</a></li>
    </ul>
    <div class="login-btn">
        <a href="#" onclick="confirmLogout(event)">Đăng xuất</a>
    </div>
</header>

<main class="dashboard">
    <h2>Kết Quả Điều Trị Của Bạn</h2>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Ngày hẹn</th>
                    <th>Giờ hẹn</th>
                    <th>Bác sĩ</th>
                    <th>Chẩn đoán</th>
                    <th>Đơn thuốc</th>
                    <th>Ghi chú</th>
                    <th>Ngày tạo</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                    <td><?= htmlspecialchars($row['appointment_time']) ?></td>
                    <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['diagnosis'])) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['prescription'] ?? '')) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['notes'] ?? '')) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Hiện chưa có kết quả điều trị nào.</p>
    <?php endif; ?>
</main>

<footer>
    <div class="footer-column">
        <h3>Liên hệ</h3>
        <p>🏥 Bệnh viện ABC</p>
        <p>Địa chỉ: Số 123 Đường Lê Duẩn, Hà Nội</p>
        <p>Điện thoại: 024.1234.5678</p>
        <p>Email: contact@benhvienabc.vn</p>
    </div>
    <div class="footer-column">
        <h3>Thông tin</h3>
        <a href="#">Giới thiệu</a>
        <a href="#">Quy trình khám bệnh</a>
        <a href="#">Hướng dẫn thanh toán</a>
        <a href="#">Hỏi đáp</a>
    </div>
    <div class="footer-column">
        <h3>Giờ làm việc</h3>
        <span>Thứ 2 - Thứ 6: 7h00 - 17h00</span>
        <span>Thứ 7, Chủ nhật: 7h30 - 12h00</span>
    </div>
    <div class="footer-bottom">
        &copy; 2025 Bệnh viện Mê Gái Xinh. Mọi quyền được bảo lưu.
    </div>
</footer>

<script>
function confirmLogout(event) {
    event.preventDefault();
    if (confirm("Bạn có chắc chắn muốn đăng xuất không?")) {
        window.location.href = "logout.php";
    }
}
</script>

</body>
</html>
