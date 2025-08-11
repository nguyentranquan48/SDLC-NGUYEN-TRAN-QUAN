<?php
session_start();
// Kiểm tra quyền truy cập
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Quản Trị - Bệnh viện ABC</title>
<style>
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f0f4f8;
    color: #333;
    line-height: 1.6;
}

/* Header */
header {
    background: linear-gradient(90deg, #004a99, #0073e6);
    padding: 15px 40px;
    box-shadow: 0 4px 12px rgba(0, 115, 230, 0.4);
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.logo {
    font-size: 28px;
    font-weight: 700;
    color: #fff;
    letter-spacing: 1.2px;
}

.menu {
    list-style: none;
    display: flex;
    gap: 30px;
    flex: 1;
    justify-content: center;
}

.menu li a {
    color: #e0e7ff;
    text-decoration: none;
    font-weight: 600;
    font-size: 17px;
    padding: 10px 18px;
    border-radius: 8px;
    transition: background-color 0.3s ease, color 0.3s ease;
    position: relative;
}

.menu li a::after {
    content: "";
    position: absolute;
    height: 3px;
    width: 0;
    background: #ffe600;
    left: 50%;
    bottom: 0;
    transform: translateX(-50%);
    transition: width 0.3s ease;
}

.menu li a:hover {
    background-color: rgba(255, 230, 0, 0.15);
    color: #fff;
}

.menu li a:hover::after {
    width: 50%;
}

/* Logout button */
.login-btn a {
    color:rgb(0, 0, 0);
    background-color:rgb(0, 255, 179);
    padding: 10px 20px;
    border-radius: 30px;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 4px 10px rgba(238, 225, 113, 0.6);
    transition: background-color 0.3s ease, color 0.3s ease;
}

.login-btn a:hover {
    background-color: #ccbb00;
    color: #002a66;
    box-shadow: 0 6px 15px rgba(204, 187, 0, 0.8);
}

/* Main container */
.container {
    max-width: 1100px;
    margin: 40px auto 70px;
    padding: 40px 30px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 115, 230, 0.15);
    color: #003366;
    text-align: center;
}

.container h2 {
    font-size: 36px;
    font-weight: 800;
    margin-bottom: 15px;
    color: #004a99;
    letter-spacing: 1.1px;
}

.container p {
    font-size: 20px;
    margin-bottom: 35px;
    color:rgb(0, 71, 252);
}

/* Grid cards */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 25px;
}

.card {
    background: linear-gradient(145deg, #e6f0ff, #ffffff);
    padding: 30px 20px;
    border-radius: 18px;
    box-shadow: 0 8px 20px rgba(0, 115, 230, 0.12);
    cursor: pointer;
    transition: transform 0.35s ease, box-shadow 0.35s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0, 115, 230, 0.25);
}

.card a {
    font-size: 20px;
    font-weight: 700;
    color: #003366;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: color 0.3s ease;
}

.card a:hover {
    color: #0073e6;
}

.card a::before {
    content: attr(data-icon);
    font-size: 28px;
}

/* Footer */
footer {
    background-color: #004a99;
    color: #cce0ff;
    padding: 40px 40px;
    margin-top: 70px;
    border-top: 6px solid #ffe600;
    font-size: 15px;
}

.footer-grid {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 40px;
}

.footer-grid .col {
    flex: 1 1 220px;
}

footer h4 {
    color: #fff056;
    margin-bottom: 15px;
    font-size: 18px;
    font-weight: 700;
}

footer p, footer a {
    color: #d1e2ff;
    line-height: 1.7;
    text-decoration: none;
    transition: color 0.3s ease;
}

footer a:hover {
    color: #fff;
    text-decoration: underline;
}

.copyright {
    text-align: center;
    padding-top: 30px;
    font-size: 14px;
    color: #ffe600;
}

/* Responsive */
@media (max-width: 768px) {
    .grid {
        grid-template-columns: 1fr;
    }

    .footer-grid {
        flex-direction: column;
        gap: 30px;
    }
}

</style>
</head>
<body>

<header>
    <div class="imgcss/icon.png"></div>

    <ul class="menu">
        <li><a href="admin.php">Trang Chủ</a></li>
        <li><a href="user_mng.php">QL Người Dùng</a></li>
        <li><a href="doctors_mng.php">QL Bác Sĩ</a></li>
        <li><a href="manage_specialties.php">QL Chuyên Khoa</a></li>
        <li><a href="appointment_mng.php">QL Lịch Khám</a></li>
        <li><a href="manage_payments.php">QL Thanh Toán</a></li>
        <li><a href="reports.php">Báo Cáo</a></li>
    </ul>

    <div class="login-btn">
    <a href="#" onclick="confirmLogout(event)">Đăng xuất</a>

    </div>
</header>

<div class="container">
    <h2>Chào mừng, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>!</h2>

    <div class="grid">
        <div class="card"><a href="user_mng.php">👥 Quản lý người dùng</a></div>
        <div class="card"><a href="doctors_mng.php">👨‍⚕️ Quản lý bác sĩ</a></div>
        <div class="card"><a href="manage_specialties.php">🏥 Quản lý chuyên khoa</a></div>
        <div class="card"><a href="appointment_mng.php">📅 Quản lý lịch khám</a></div>
        <div class="card"><a href="manage_payments.php">💳 Quản lý thanh toán</a></div>
        <div class="card"><a href="reports.php">📊 Báo cáo thống kê</a></div>
    </div>
</div>

<footer>
    <div class="footer-grid">
        <div class="col">
            <h4>Liên hệ</h4>
            <p>🏥 Bệnh viện ABC</p>
            <p>Địa chỉ: Số 123 Đường Lê Duẩn, Hà Nội</p>
            <p>Điện thoại: 024.1234.5678</p>
            <p>Email: contact@benhvienabc.vn</p>
        </div>
        <div class="col">
            <h4>Thông tin</h4>
            <p><a href="#">Giới thiệu</a></p>
            <p><a href="#">Quy trình khám bệnh</a></p>
            <p><a href="#">Hướng dẫn thanh toán</a></p>
            <p><a href="#">Hỏi đáp</a></p>
        </div>
        <div class="col">
            <h4>Giờ làm việc</h4>
            <p>Thứ 2 - Thứ 6: 7h00 - 17h00</p>
            <p>Thứ 7, Chủ nhật: 7h30 - 12h00</p>
        </div>
    </div>
    <div class="copyright">
        &copy; 2025 Bệnh viện Mê Gái Xinh - Trang quản trị dành cho Admin.
    </div>
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
