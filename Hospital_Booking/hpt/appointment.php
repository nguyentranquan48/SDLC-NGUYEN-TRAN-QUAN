<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];
$message = "";

// Lấy tên file hiện tại để highlight menu
$current_page = basename($_SERVER['PHP_SELF']);

// Lấy danh sách chuyên khoa
$specialties_result = $conn->query("SELECT * FROM specialties ORDER BY name");

// Xử lý đặt lịch
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specialty_id = $_POST['specialty'] ?? null;
    $doctor_id = $_POST['doctor'] ?? null;
    $appointment_date = $_POST['appointment_date'] ?? null;
    $appointment_time = $_POST['appointment_time'] ?? null;

    if ($specialty_id && $doctor_id && $appointment_date && $appointment_time) {
        $check = $conn->prepare("SELECT * FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status = 'confirmed'");
        $check->bind_param("iss", $doctor_id, $appointment_date, $appointment_time);
        $check->execute();
        $result_check = $check->get_result();

        if ($result_check->num_rows > 0) {
            $message = "❌ Thời gian này đã có người đặt. Vui lòng chọn thời gian khác.";
        } else {
            $insert = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, specialty_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $insert->bind_param("iiiss", $patient_id, $doctor_id, $specialty_id, $appointment_date, $appointment_time);
            if ($insert->execute()) {
                $message = "✅ Đặt lịch thành công! Vui lòng chờ xác nhận.";
            } else {
                $message = "❌ Có lỗi xảy ra. Vui lòng thử lại.";
            }
        }
    } else {
        $message = "❗ Vui lòng điền đầy đủ thông tin.";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt lịch khám - Bệnh viện ABC</title>
    <style>
        /* Giữ nguyên CSS form */
        form {
            background-color: #008c99;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            gap: 15px;
            color: #fff;
        }
        form label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #fff9c4;
        }
        form select,
        form input[type="date"],
        form input[type="time"] {
            padding: 10px 12px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            background-color: #ffffff;
            color: #333;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
            transition: 0.2s;
        }
        form select:focus,
        form input:focus {
            outline: none;
            border: 2px solid #ffcc00;
            box-shadow: 0 0 5px #ffcc00;
        }
        form button {
            padding: 12px;
            background-color: #ffb703;
            color: #000;
            border: none;
            font-weight: bold;
            font-size: 16px;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        form button:hover {
            background-color: #ffa600;
        }
        .message {
            background-color: rgba(255,255,255,0.1);
            padding: 12px;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            border: 1px solid #ffe066;
            max-width: 500px;
            margin: 0 auto 20px;
            text-align: center;
        }

        /* CSS chung */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #01eeff; color: #febc37; line-height: 1.6; }
        header { background-color:rgb(35, 255, 1); padding: 15px 40px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); display: flex; align-items: center; justify-content: space-between; }
        .logo img { height: 120px; border-radius: 20px; }
        .menu { list-style: none; display: flex; gap: 25px; flex: 1; justify-content: center; }
        .menu li a { color: #000000; text-decoration: none; font-weight: 600; font-size: 16px; padding: 10px 16px; border-radius: 6px; position: relative; transition: 0.3s ease; }
        .menu li a::after { content: ""; position: absolute; height: 3px; width: 0; background: #fff; left: 50%; bottom: 0; transform: translateX(-50%); transition: 0.3s ease; }
        .menu li a:hover::after { width: 60%; }
        .menu li a.active::after { width: 60%; }
        .menu li a.active { background-color: rgba(255, 255, 255, 0.15); }
        .login-btn a { color: white; background-color: #8401ff; padding: 10px 16px; border-radius: 6px; font-weight: bold; text-decoration: none; transition: 0.3s ease; }
        .login-btn a:hover { background-color: #6a040f; }
        .dashboard { max-width: 1100px; margin: 30px auto 50px; padding: 30px; background-color: #00c2d1cc; border-radius: 15px; box-shadow: 0 6px 18px rgba(0,0,0,0.15); color: #fffbe6; }
        .dashboard h2 { font-size: 30px; margin-bottom: 10px; font-weight: 700; text-shadow: 0 1px 3px rgba(0,0,0,0.5); }
        .dashboard p { font-size: 18px; margin-bottom: 25px; color: #ffeb99; text-shadow: 0 1px 2px rgba(0,0,0,0.3); }

        footer { background-color: #d62828; color: #fff; padding: 30px 40px; margin-top: 50px; border-top: 6px solid #9d0208; }
        .footer-grid { display: flex; justify-content: space-between; flex-wrap: wrap; }
        .footer-grid .col { flex: 1; min-width: 220px; margin-right: 20px; }
        footer h4 { color: #ffd700; margin-bottom: 12px; font-size: 17px; }
        footer p, footer a { font-size: 14px; color: #eee; line-height: 1.6; text-decoration: none; }
        footer a:hover { text-decoration: underline; color: #ffcc00; }
        .copyright { text-align: center; padding-top: 20px; font-size: 13px; color: #ccc; }
    </style>
    <script>
        function loadDoctors(specialtyId) {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "get_doctors.php?specialty_id=" + specialtyId, true);
            xhr.onload = function () {
                if (this.status === 200) {
                    const doctors = JSON.parse(this.responseText);
                    const doctorSelect = document.getElementById("doctor");
                    doctorSelect.innerHTML = '<option value="">-- Chọn bác sĩ --</option>';
                    doctors.forEach(function (doc) {
                        const option = document.createElement("option");
                        option.value = doc.doctor_id;
                        option.textContent = doc.full_name;
                        doctorSelect.appendChild(option);
                    });
                }
            };
            xhr.send();
        }
        function confirmLogout(event) {
            event.preventDefault();
            if (confirm("Bạn có chắc chắn muốn đăng xuất không?")) {
                window.location.href = "logout.php";
            }
        }
    </script>
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
    <h2 style="text-align:center;">🗓️ Đặt lịch khám</h2>
    <p style="text-align:center;">Chọn chuyên khoa, bác sĩ và thời gian khám phù hợp với bạn.</p>

    <?php if ($message): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST" style="max-width: 500px; margin: 30px auto 0;">
        <label>Chuyên khoa:</label>
        <select name="specialty" required onchange="loadDoctors(this.value)">
            <option value="">-- Chọn chuyên khoa --</option>
            <?php while ($row = $specialties_result->fetch_assoc()): ?>
                <option value="<?= $row['specialty_id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>Bác sĩ:</label>
        <select name="doctor" id="doctor" required>
            <option value="">-- Chọn bác sĩ --</option>
        </select>

        <label>Ngày khám:</label>
        <input type="date" name="appointment_date" required min="<?= date('Y-m-d') ?>">

        <label>Giờ khám:</label>
        <input type="time" name="appointment_time" required>

        <button type="submit">Xác nhận đặt lịch</button>
    </form>
</main>

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
        &copy; 2025 Bệnh viện Mê Gái Xinh. Mọi quyền được bảo lưu.
    </div>
</footer>

</body>
</html>
