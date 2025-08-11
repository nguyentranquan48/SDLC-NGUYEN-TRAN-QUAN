<?php
// Kết nối CSDL nếu cần
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống quản lý bệnh nhân</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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

        /* Header */
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 40px;
            background-color:rgb(132, 255, 0);
            color: #000000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
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

        .menu li a:hover {
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

        /* Main layout */
        .main-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .left-box video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .left-box {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .left-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .right-box {
            display: grid;
            grid-template-rows: 1fr 1fr;
            gap: 20px;
        }

        .top-right-box {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .top-right-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .bottom-right-box {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            overflow-y: auto;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .bottom-right-box h3 {
            color: #d62828;
            margin-bottom: 12px;
            font-size: 20px;
        }

        .bottom-right-box p {
            margin-bottom: 12px;
            font-size: 15px;
            border-left: 4px solid #d62828;
            padding-left: 10px;
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
            form.search-form, form.payment-form {
                padding: 16px 20px;
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
    <div class="logo">
        <img src="imgcss/icon.png" alt="Logo">
    </div>

    <ul class="menu">
        <li><a href="trang_chu1.php">Trang Chủ</a></li>
        <li><a href="login.php">Đặt Lịch</a></li>
        <li><a href="doctor_list.php">Đội Ngũ Bác Sĩ</a></li>
        <li><a href="login.php">Hồ Sơ Bệnh Nhân</a></li>
        <li><a href="login.php">Lịch Sử</a></li>
    </ul>
    <div class="login-btn">
        <a href="login.php">Đăng nhập</a>
    </div>
</header>

<div class="main-layout">
    <div class="left-box">
        <!-- Video chạy tự động, lặp vô hạn, không tiếng -->
        <video src="imgcss/video1.MOV" autoplay loop muted playsinline></video>
    </div>

    <div class="right-box">
        <div class="top-right-box">
            <img src="imgcss/quan2.jpg" alt="Slide phải trên">
        </div>
        <div class="bottom-right-box">
            <h3>Tin tức y tế nổi bật</h3>
            <p><strong>💉 Vaccine mới cho bệnh hô hấp:</strong> Bộ Y tế vừa phê duyệt loại vaccine mới cho người cao tuổi.</p>
            <p><strong>🧪 Công nghệ AI trong chẩn đoán:</strong> Nhiều bệnh viện lớn đã ứng dụng AI hỗ trợ chẩn đoán nhanh hơn.</p>
            <p><strong>🏥 Mở rộng bệnh viện tuyến huyện:</strong> Gần 20 trung tâm y tế mới đang được xây dựng tại các tỉnh miền núi.</p>
            <p><strong>👩‍⚕️ Thêm hơn 500 bác sĩ được đào tạo chuyên sâu:</strong> Tăng cường chất lượng điều trị tại tuyến cơ sở.</p>
            <p><strong>🫀 Ghép tạng thành công đầu tiên tại miền Trung:</strong> Bệnh nhân hồi phục tốt sau 2 tuần phẫu thuật.</p>
            <p><strong>📈 Báo cáo y tế 2025:</strong> Tỷ lệ tiêm chủng tăng 12% so với năm trước, vượt chỉ tiêu quốc gia.</p>
            <p><strong>🧫 Phát hiện vi khuẩn kháng thuốc mới:</strong> WHO cảnh báo cần giám sát chặt tại các bệnh viện.</p>
        </div>
    </div>
</div>

<footer>
    <div class="footer-grid">
        <div>
            <h4>Về Bệnh viện Mê Gái Xinh</h4>
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
            <p><a href="trang_chu1.php">Trang Chủ</a></p>
            <p><a href="Login.php">Đặt Lịch</a></p>
            <p><a href="doctors_list.php">Đội Ngũ Bác Sĩ</a></p>
            <p><a href="login.php">Hồ Sơ Bệnh Nhân</a></p>
            <p><a href="login.php">Lịch Sử</a></p>
        </div>
    </div>
    <div class="copyright">&copy; 2025 Bệnh viện Mê Gái Xinh. Bản quyền thuộc về chúng tôi.</div>
</footer>

</body>
</html>
