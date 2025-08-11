<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$current_page = basename($_SERVER['PHP_SELF']); // Lấy tên file hiện tại

// Xử lý form cập nhật hồ sơ
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';

    $stmt = $conn->prepare("UPDATE users SET full_name=?, phone=?, address=? WHERE user_id=?");
    $stmt->bind_param("sssi", $full_name, $phone, $address, $user_id);
    if ($stmt->execute()) {
        $message = "Cập nhật hồ sơ thành công.";
    } else {
        $message = "Có lỗi xảy ra khi cập nhật.";
    }
    $stmt->close();
}

// Lấy thông tin người dùng
$stmt = $conn->prepare("SELECT username, full_name, phone, address FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>Hồ sơ cá nhân - Bệnh viện ABC</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color:rgb(2, 255, 247);
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
        .menu {
            list-style: none;
            display: flex;
            gap: 25px;
            flex: 1;
            justify-content: center;
        }
        .menu li a {
            color: #000;
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
        /* Hover & Active */
        .menu li a:hover::after,
        .menu li a.active::after {
            width: 60%;
        }
        .menu li a:hover,
        .menu li a.active {
            background-color: rgba(255, 255, 255, 0.15);
        }
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
        main.dashboard {
            max-width: 600px;
            margin: 30px auto 50px;
            padding: 30px;
            background-color:rgba(4, 5, 5, 0.8);
            border-radius: 15px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
            color: #fffbe6;
        }
        main.dashboard h2 {
            font-size: 28px;
            margin-bottom: 20px;
            font-weight: 700;
            text-align: center;
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
        }
        input[type="text"], input[type="tel"], textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: none;
            outline: none;
        }
        input[disabled] {
            background-color: #ddd;
            color: #666;
        }
        button {
            margin: 20px auto 0 auto;
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            background-color: #febc37;
            font-weight: 700;
            cursor: pointer;
            color: #000;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #d4a017;
        }
        .message {
            margin-top: 20px;
            text-align: center;
            font-weight: 700;
            color: #00ff00;
        }
        /* Footer */
        footer {
            background-color:rgb(255, 140, 142);
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
            padding: 0 15px;
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
            border-top: 1px solid rgba(255, 188, 55, 0.3);
            padding-top: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>
<header>
    <div class="logo">
        <img src="imgcss/icon.png" alt="Logo Bệnh viện ABC" />
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
    <h2>Hồ Sơ Cá Nhân</h2>
    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label>Tên đăng nhập:</label>
        <input type="text" value="<?= htmlspecialchars($user['username'] ?? '') ?>" disabled />

        <label>Họ tên:</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required />

        <label>Số điện thoại:</label>
        <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required />

        <label>Địa chỉ:</label>
        <textarea name="address" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>

        <button type="submit">Cập nhật</button>
    </form>
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
