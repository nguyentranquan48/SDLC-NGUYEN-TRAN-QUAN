<?php
// Kết nối CSDL
$conn = new mysqli("localhost", "root", "", "hospital_mng");
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Truy vấn kết hợp 3 bảng: users + doctors + specialties
$sql = "SELECT u.user_id, u.full_name, u.phone, u.address, d.specialty_id, d.img, s.name AS specialty_name 
        FROM users u 
        JOIN doctors d ON u.user_id = d.user_id 
        JOIN specialties s ON d.specialty_id = s.specialty_id 
        WHERE u.role = 'doctor'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách Bác sĩ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background:rgb(0, 237, 226);
            padding: 20px;
            margin: 0;
        }

        header {
            background-color:rgb(97, 248, 47);  
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
            gap: 20px;
            padding: 0;
            margin: 0;
        }

        .menu li {
            display: inline;
        }

        .menu a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }

        .menu a:hover {
            color: red;
        }

        .login-btn a {
            text-decoration: none;
            padding: 6px 12px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
        }

        .login-btn a:hover {
            background-color: #0056b3;
        }

        h1 {
            margin-top: 30px;
            color: #fff;
            text-align: center;
        }

        .doctor-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .doctor-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            transition: transform 0.2s ease;
        }

        .doctor-card:hover {
            transform: translateY(-5px);
        }

        .doctor-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .doctor-info {
            padding: 15px;
        }

        .doctor-info h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        .doctor-info p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
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

<!-- HEADER + MENU -->
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

<!-- DANH SÁCH BÁC SĨ -->
<h1>Danh sách Bác sĩ</h1>
<div class="doctor-list">
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Chuyển đường dẫn ảnh từ "img/doctors" sang "imgcss"
            $img = !empty($row['img']) ? str_replace("img/doctors", "imgcss", $row['img']) : 'imgcss/default.jpg';

            echo "<div class='doctor-card'>
                    <img src='{$img}' alt='Ảnh bác sĩ'>
                    <div class='doctor-info'>
                        <h3>{$row['full_name']}</h3>
                        <p><strong>Chuyên khoa:</strong> {$row['specialty_name']}</p>
                        <p><strong>Điện thoại:</strong> {$row['phone']}</p>
                        <p><strong>Địa chỉ:</strong> {$row['address']}</p>
                    </div>
                </div>";
        }
    } else {
        echo "<p>Không có dữ liệu bác sĩ.</p>";
    }
    $conn->close();
    ?>
</div>

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
            <p><a href="trang_chu1.php">Trang Chủ</a></p>
            <p><a href="Login.php">Đặt Lịch</a></p>
            <p><a href="doctor_list.php">Đội Ngũ Bác Sĩ</a></p>
            <p><a href="login.php">Hồ Sơ Bệnh Nhân</a></p>
            <p><a href="login.php">Lịch Sử</a></p>
        </div>
    </div>
    <div class="copyright">&copy; 2025 Bệnh viện Mê Gái Xinh. Bản quyền thuộc về chúng tôi.</div>
</footer>

</body>
</html>
