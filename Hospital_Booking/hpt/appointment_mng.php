<?php
session_start();
include 'connect.php';

// Kiểm tra đăng nhập và quyền admin (nếu cần)
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$search = $_GET['search'] ?? '';

// Xử lý nút hành động xác nhận, huỷ, xoá
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['appointment_id'])) {
    $id = (int)$_POST['appointment_id'];

    if (isset($_POST['confirm'])) {
        $stmt = $conn->prepare("UPDATE appointments SET status = 'confirmed' WHERE appointment_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif (isset($_POST['cancel'])) {
        $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif (isset($_POST['delete'])) {
        $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Lấy dữ liệu lịch khám theo tìm kiếm (tên hoặc ngày)
$sql = "
SELECT a.*, 
       u.full_name AS patient_name, 
       d.full_name AS doctor_name, 
       s.name AS specialty_name 
FROM appointments a
JOIN users u ON a.patient_id = u.user_id
JOIN doctors d ON a.doctor_id = d.doctor_id
JOIN specialties s ON a.specialty_id = s.specialty_id
WHERE u.full_name LIKE ? OR a.appointment_date = ?
ORDER BY a.appointment_date DESC, a.appointment_time DESC
";

$stmt = $conn->prepare($sql);
$searchLike = "%" . $search . "%";
$stmt->bind_param("ss", $searchLike, $search);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>Quản lý Lịch Khám - Bệnh viện ABC</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to bottom right, #f0f8ff, #e6f7ff);
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background: #005bbb;
            padding: 16px 32px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .menu {
            display: flex;
            gap: 24px;
        }

        .menu a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            transition: background 0.3s;
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
        }
        .login-btn a:hover {
            background: #e6c200;
        }

        main.container {
            background: #fff;
            padding: 40px;
            margin: 30px auto;
            border-radius: 16px;
            box-shadow: 0 0 24px rgba(0,0,0,0.1);
            max-width: 1200px;
            flex-grow: 1;
        }

        h2 {
            color: #005bbb;
            margin-bottom: 20px;
            font-size: 28px;
            text-align: center;
        }

        form.search-form {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 12px;
            flex-wrap: wrap;
        }

        form.search-form input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 300px;
            font-size: 16px;
        }

        form.search-form button {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            background: #007bff;
            color: white;
            transition: background 0.3s;
        }
        form.search-form button:hover {
            background: #0069d9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #f9f9f9;
            table-layout: fixed;
            word-wrap: break-word;
        }

        th, td {
            padding: 14px 10px;
            border-bottom: 1px solid #ccc;
            text-align: center;
        }

        thead {
            background: #007bff;
            color: white;
        }

        tbody tr:hover {
            background: #eaf6ff;
        }

        button.action-btn {
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            margin: 2px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        button.confirm { background: #28a745; color: white; }
        button.confirm:hover { background: #218838; }

        button.cancel { background: #dc3545; color: white; }
        button.cancel:hover { background: #c82333; }

        button.delete { background: #6c757d; color: white; }
        button.delete:hover { background: #5a6268; }

        .status-pending { color: orange; font-weight: bold; }
        .status-confirmed { color: green; font-weight: bold; }
        .status-cancelled { color: red; font-weight: bold; }

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
        <a href="appointment_mng.php" class="active">QL Lịch Khám</a>
        <a href="manage_payments.php">QL Thanh Toán</a>
        <a href="reports.php">Báo Cáo</a>
    </nav>
    <a href="#" onclick="confirmLogout(event)">Đăng xuất</a>

</header>

<main class="container">
    <h2>📋 Quản lý Lịch Khám</h2>

    <form method="GET" class="search-form" autocomplete="off">
        <input type="text" name="search" placeholder="Tìm theo tên bệnh nhân hoặc ngày (YYYY-MM-DD)" 
            value="<?= htmlspecialchars($search) ?>" />
        <button type="submit">🔍 Tìm kiếm</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Họ tên bệnh nhân</th>
                <th>Bác sĩ</th>
                <th>Chuyên khoa</th>
                <th>Ngày</th>
                <th>Giờ</th>
                <th>Trạng thái</th>
                <th>Thời gian đặt</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['patient_name']) ?></td>
                    <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                    <td><?= htmlspecialchars($row['specialty_name']) ?></td>
                    <td><?= $row['appointment_date'] ?></td>
                    <td><?= $row['appointment_time'] ?></td>
                    <td class="status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>" />
                            <?php if ($row['status'] === 'pending'): ?>
                                <button type="submit" name="confirm" class="action-btn confirm">Xác nhận</button>
                                <button type="submit" name="cancel" class="action-btn cancel">Huỷ</button>
                            <?php endif; ?>
                            <button type="submit" name="delete" class="action-btn delete" 
                                onclick="return confirm('Bạn có chắc muốn xoá?')">Xoá</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8">Không tìm thấy lịch khám nào.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
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
