<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

// Lấy tên file hiện tại
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Người Dùng - Bệnh Viện ABC</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #01eeff;
            color: #febc37;
            line-height: 1.6;
        }

        header {
            background-color: rgb(35, 255, 1);
            padding: 15px 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
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
            color: #000000;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            padding: 10px 16px;
            border-radius: 6px;
            position: relative;
            transition: 0.3s ease;
        }

        /* Gạch chân khi hover */
        .menu li a::after {
            content: "";
            position: absolute;
            height: 3px;
            width: 0;
            background: #fff;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            transition: 0.3s ease;
        }

        .menu li a:hover::after {
            width: 60%;
        }

        /* Gạch chân cho trang đang active */
        .menu li a.active::after {
            width: 60%;
        }

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

        .hero-image {
            width: 100%;
            height: 260px;
            background-image: url('https://png.pngtree.com/png-vector/20190119/ourlarge/pngtree-hospital-cartoon-cartoon-hospital-helicopter-png-image_479196.jpg');
            background-size: cover;
            background-position: center;
            border-bottom: 4px solid #febc37;
        }

        .dashboard {
            max-width: 1100px;
            margin: 30px auto 50px;
            padding: 30px;
            background-color: #00c2d1cc;
            border-radius: 15px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
            color: #fffbe6;
        }

        .dashboard h2 {
            font-size: 30px;
            margin-bottom: 10px;
            font-weight: 700;
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }

        .dashboard p {
            font-size: 18px;
            margin-bottom: 25px;
            color: #ffeb99;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        .quick-links {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 50px;
        }

        .card {
            flex: 1 1 calc(20% - 20px);
            min-width: 180px;
            max-width: 220px;
            background: #111;
            padding: 30px 15px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-top: 5px solid #febc37;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.5);
        }

        .card a {
            color: #fffbe6;
            font-size: 18px;
            font-weight: 700;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .quick-links {
                flex-direction: column;
                align-items: center;
            }

            .card {
                flex: 1 1 100%;
                max-width: 90%;
            }
        }

        .news-section h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 25px;
            text-shadow: 0 1px 3px rgba(0,0,0,0.4);
        }

        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit,minmax(320px,1fr));
            gap: 30px;
        }

        .news-card {
            background: #022f40dd;
            border-radius: 15px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.3);
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }

        .news-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.6);
        }

        .news-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .news-content {
            padding: 15px 20px 25px;
            color: #f0f0f0;
        }

        .news-content h4 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .news-content h4 a {
            color: #ffd43b;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .news-content h4 a:hover {
            color: #ffa500;
        }

        .news-content p {
            font-size: 15px;
            color: #e0e0e0cc;
            line-height: 1.4;
        }

        footer {
            background-color: #d62828;
            color: #fff;
            padding: 30px 40px;
            margin-top: 50px;
            border-top: 6px solid #9d0208;
        }

        .footer-grid {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .footer-grid .col {
            flex: 1;
            min-width: 220px;
            margin-right: 20px;
        }

        footer h4 {
            color: #ffd700;
            margin-bottom: 12px;
            font-size: 17px;
        }

        footer p, footer a {
            font-size: 14px;
            color: #eee;
            line-height: 1.6;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
            color: #ffcc00;
        }

        .copyright {
            text-align: center;
            padding-top: 20px;
            font-size: 13px;
            color: #ccc;
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

<div class="hero-image"></div>

<main class="dashboard">
    <h2>Xin chào, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Người dùng'); ?>!</h2>
    <p>Chào mừng bạn đến với hệ thống đặt lịch khám bệnh trực tuyến của Bệnh viện Mê Gái Xinh.</p>

    <section class="quick-links">
        <div class="card"><a href="appointment.php">📅 Đặt lịch khám</a></div>
        <div class="card"><a href="profile.php">👤 Hồ sơ cá nhân</a></div>
        <div class="card"><a href="appointment_history.php">📊 Lịch sử khám</a></div>
        <div class="card"><a href="payment.php">💳 Thanh toán</a></div>
        <div class="card"><a href="treatment_results.php">🧪 Kết quả điều trị</a></div>
    </section>
    <section class="news-section">
        <h3>Tin tức nổi bật</h3>
        <div class="news-grid">
            <article class="news-card">
                <img src="imgcss/padc.jpg" alt="Tin 1">
                <div class="news-content">
                    <h4><a href="https://www.pinterest.com/vitbau21825/%E1%BA%A3nh-m%E1%BA%A1ng-girl-xinh/">Cập nhật phương pháp điều trị mới tại Bệnh viện Mê Gái Xinh</a></h4>
                    <p>Phương pháp điều trị hiện đại giúp rút ngắn thời gian phục hồi...</p>
                </div>
            </article>
            <article class="news-card">
                <img src="imgcss/he.jpg" alt="Tin 2">
                <div class="news-content">
                    <h4><a href="#">Chăm sóc sức khỏe mùa hè: Những điều cần lưu ý</a></h4>
                    <p>Đảm bảo sức khỏe trong mùa nóng là điều cần thiết để phòng tránh bệnh...</p>
                </div>
            </article>
            <article class="news-card">
                <img src="imgcss/kham-tong-quat-1.jpg" alt="Tin 3">
                <div class="news-content">
                    <h4><a href="#">Khám sức khỏe tổng quát – Bạn đã làm chưa?</a></h4>
                    <p>Khám sức khỏe định kỳ giúp phát hiện sớm các bệnh lý tiềm ẩn...</p>
                </div>
            </article>
        </div>
    </section>
</main>

<footer>
    <div class="footer-grid">
        <div class="col">
            <h4>Liên hệ</h4>
            <p>🏥 Bệnh viện Mê Gái</p>
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
