<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];

// Lấy danh sách lịch khám của bệnh nhân
$sql = "
    SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status, 
           d.full_name AS doctor_name, s.name AS specialty_name
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.doctor_id
    JOIN specialties s ON a.specialty_id = s.specialty_id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

// Xử lý hủy lịch khi có yêu cầu
$message = "";
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $cancel_id = intval($_GET['cancel']);
    // Kiểm tra xem lịch có thuộc bệnh nhân và trạng thái có cho phép hủy
    $check = $conn->prepare("SELECT status FROM appointments WHERE appointment_id = ? AND patient_id = ?");
    $check->bind_param("ii", $cancel_id, $patient_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        if (in_array($row['status'], ['pending', 'confirmed'])) {
            // Cập nhật trạng thái thành 'cancelled'
            $update = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ?");
            $update->bind_param("i", $cancel_id);
            if ($update->execute()) {
                $message = "Hủy lịch khám thành công.";
                header("Location: appointment_history.php?msg=" . urlencode($message));
                exit();
            } else {
                $message = "Hủy lịch thất bại, vui lòng thử lại.";
            }
        } else {
            $message = "Lịch khám này không thể hủy.";
        }
    } else {
        $message = "Lịch khám không hợp lệ hoặc không tồn tại.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>Lịch sử khám - Bệnh viện ABC</title>
    <style>
        /* Toàn bộ CSS giống với trang đặt lịch khám để đồng bộ header/menu/footer */

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
            background-color:rgb(35, 255, 1);
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

        main {
            max-width: 1100px;
            margin: 30px auto 50px;
            padding: 30px;
            background-color: #00c2d1cc;
            border-radius: 15px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
            color: #fffbe6;
        }

        main h2 {
            font-size: 30px;
            margin-bottom: 25px;
            font-weight: 700;
            text-align: center;
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
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
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fffbe6;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            color: #333;
            font-weight: 600;
        }

        th, td {
            padding: 15px 20px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #8401ff;
            color: white;
            font-weight: 700;
        }

        tr:hover {
            background-color: #f0f0f0;
        }

        .status {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            color: white;
            display: inline-block;
            min-width: 80px;
        }

        .pending { background-color: #ffc107; color: #212529; }
        .confirmed { background-color: #28a745; }
        .cancelled { background-color: #dc3545; }
        .completed { background-color: #17a2b8; }

        .btn-cancel {
            color: #dc3545;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .btn-cancel:hover {
            text-decoration: underline;
            color: #a71d2a;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 25px;
            text-decoration: none;
            color: #8401ff;
            border: 2px solid #8401ff;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s, color 0.3s;
        }

        .back-button:hover {
            background-color: #8401ff;
            color: white;
        }

        /* Footer mới theo hình */
       /* Footer mới theo hình (đã chỉnh lại để chuẩn hơn) */
footer {
    background-color: #a41215;
    color: #febc37;
    padding: 40px 60px;
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    font-weight: 600;
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
    transition: color 0.3s ease;
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
    letter-spacing: 0.05em;
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
        <li><a href="patient.php">Trang Chủ</a></li>
        <li><a href="appointment.php">Đặt Lịch</a></li>
        <li><a href="appointment_history.php" class="active">Lịch Sử</a></li>
        <li><a href="payment.php">Thanh Toán</a></li>
        <li><a href="treatment_results.php">Kết Quả</a></li>
        <li><a href="profile.php">Hồ Sơ</a></li>
    </ul>

    <div class="login-btn">
    <a href="#" onclick="confirmLogout(event)">Đăng xuất</a>

    </div>
</header>

<main>
    <h2>Lịch sử khám của bạn</h2>

    <?php
    if (!empty($_GET['msg'])) {
        echo "<p class='message'>" . htmlspecialchars($_GET['msg']) . "</p>";
    } elseif (!empty($message)) {
        echo "<p class='message'>" . htmlspecialchars($message) . "</p>";
    }
    ?>

    <table>
        <thead>
            <tr>
                <th>Chuyên khoa</th>
                <th>Bác sĩ</th>
                <th>Ngày khám</th>
                <th>Thời gian</th>
                <th>Trạng thái</th>
                <th>Hủy lịch</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $status_class = "";
                    switch ($row['status']) {
                        case 'pending': $status_class = "pending"; break;
                        case 'confirmed': $status_class = "confirmed"; break;
                        case 'cancelled': $status_class = "cancelled"; break;
                        case 'completed': $status_class = "completed"; break;
                        default: $status_class = "";
                    }
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['specialty_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['doctor_name']) . "</td>";
                    echo "<td>" . date('d/m/Y', strtotime($row['appointment_date'])) . "</td>";
                    echo "<td>" . htmlspecialchars($row['appointment_time']) . "</td>";
                    echo "<td><span class='status $status_class'>" . ucfirst($row['status']) . "</span></td>";
                    if (in_array($row['status'], ['pending', 'confirmed'])) {
                        echo "<td><a class='btn-cancel' href='?cancel=" . $row['appointment_id'] . "' onclick='return confirm(\"Bạn có chắc muốn hủy lịch này?\");'>Hủy</a></td>";
                    } else {
                        echo "<td>-</td>";
                    }
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>Bạn chưa có lịch khám nào.</td></tr>";
            }
            ?>
        </tbody>
    </table>
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
