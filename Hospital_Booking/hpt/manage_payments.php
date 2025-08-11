<?php
session_start();
include 'connect.php';

// Kiểm tra đăng nhập admin (nếu cần)
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Tìm kiếm theo tên bệnh nhân
$search = $_GET['search'] ?? '';
$sql = "SELECT payments.*, users.full_name AS patient_name 
        FROM payments
        JOIN appointments ON payments.appointment_id = appointments.appointment_id
        JOIN users ON appointments.patient_id = users.user_id
        WHERE users.full_name LIKE ? 
        ORDER BY payments.payment_date DESC";
$stmt = $conn->prepare($sql);
$searchTerm = "%$search%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// Lấy danh sách lịch hẹn để hiển thị trong form thêm/sửa
$appointments = $conn->query("SELECT a.appointment_id, u.full_name 
                              FROM appointments a
                              JOIN users u ON a.patient_id = u.user_id");

// Thêm thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $appointment_id = $_POST['appointment_id'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO payments (appointment_id, amount, payment_method, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $appointment_id, $amount, $payment_method, $status);
    $stmt->execute();
    header("Location: manage_payments.php");
    exit();
}

// Xoá thanh toán
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM payments WHERE payment_id = $id");
    header("Location: manage_payments.php");
    exit();
}

// Cập nhật thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['payment_id'];
    $appointment_id = $_POST['appointment_id'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE payments SET appointment_id=?, amount=?, payment_method=?, status=? WHERE payment_id=?");
    $stmt->bind_param("idssi", $appointment_id, $amount, $payment_method, $status, $id);
    $stmt->execute();
    header("Location: manage_payments.php");
    exit();
}

// Lấy dữ liệu thanh toán để sửa
$editPayment = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $editPayment = $conn->query("SELECT * FROM payments WHERE payment_id = $id")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>Quản lý Thanh Toán - Bệnh viện ABC</title>
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
            max-width: 1200px;
            flex-grow: 1;
            width: 95%;
        }
        h2 {
            color: #005bbb;
            margin-bottom: 20px;
            font-size: 28px;
            text-align: center;
        }
        form.search-form, form.payment-form {
            max-width: 600px;
            margin: 0 auto 40px auto;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            justify-content: center;
            background: #f9faff;
            padding: 24px 32px;
            border-radius: 16px;
            box-shadow: 0 0 12px rgba(0,0,0,0.05);
            transition: box-shadow 0.3s ease;
        }
        form.search-form:hover, form.payment-form:hover {
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        form.search-form input[type="text"], 
        form.payment-form input[type="text"], 
        form.payment-form input[type="number"], 
        form.payment-form select {
            flex: 1 1 100%;
            padding: 12px 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        form.search-form input[type="text"]:focus, 
        form.payment-form input[type="text"]:focus, 
        form.payment-form input[type="number"]:focus, 
        form.payment-form select:focus {
            border-color: #005bbb;
            outline: none;
        }
        form.payment-form label {
            flex: 1 1 100%;
            font-weight: 600;
            margin-top: 12px;
            color: #333;
        }
        form.search-form button, form.payment-form input[type="submit"] {
            padding: 14px 32px;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            background: #007bff;
            color: white;
            transition: background 0.3s ease;
            flex: 1 1 auto;
            max-width: 200px;
            align-self: flex-end;
            white-space: nowrap;
        }
        form.search-form button:hover, form.payment-form input[type="submit"]:hover {
            background: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #f9f9f9;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 16px 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            word-break: break-word;
        }
        thead th {
            background: #007bff;
            color: white;
            font-weight: 600;
            text-align: center;
        }
        tbody tr:hover {
            background: #eaf6ff;
        }
        tbody td {
            vertical-align: middle;
        }
        .actions a {
            margin-right: 12px;
            color: #007BFF;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            user-select: none;
        }
        .actions a:hover {
            text-decoration: underline;
        }
        .status-pending { color: orange; font-weight: 700; text-transform: capitalize;}
        .status-completed { color: green; font-weight: 700; text-transform: capitalize;}
        .status-failed { color: red; font-weight: 700; text-transform: capitalize;}
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
    <div><strong></strong></div>
    <nav class="menu">
        <a href="admin.php">Trang Chủ</a>
        <a href="user_mng.php">QL Người Dùng</a>
        <a href="doctors_mng.php">QL Bác Sĩ</a>
        <a href="manage_specialties.php">QL Chuyên Khoa</a>
        <a href="appointment_mng.php">QL Lịch Khám</a>
        <a href="manage_payments.php" class="active">QL Thanh Toán</a>
        <a href="reports.php">Báo Cáo</a>
    </nav>
    <a href="#" onclick="confirmLogout(event)">Đăng xuất</a>

</header>

<main class="container">
    <h2>🔍 Tìm kiếm thanh toán</h2>
    <form method="GET" class="search-form" autocomplete="off">
        <input type="text" name="search" placeholder="Nhập tên bệnh nhân..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Tìm</button>
    </form>

    <h2><?= $editPayment ? "✏️ Sửa thanh toán" : "➕ Thêm thanh toán" ?></h2>
    <form method="POST" class="payment-form" autocomplete="off">
        <input type="hidden" name="payment_id" value="<?= $editPayment['payment_id'] ?? '' ?>">

        <label for="appointment_id">Lịch hẹn:</label>
        <select name="appointment_id" id="appointment_id" required>
            <option value="">-- Chọn lịch hẹn --</option>
            <?php 
            // Reset pointer trước khi lặp
            $appointments->data_seek(0);
            while ($row = $appointments->fetch_assoc()): ?>
                <option value="<?= $row['appointment_id'] ?>"
                    <?= isset($editPayment) && $editPayment['appointment_id'] == $row['appointment_id'] ? 'selected' : '' ?>>
                    <?= "ID: {$row['appointment_id']} - {$row['full_name']}" ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="amount">Số tiền (VNĐ):</label>
        <input type="number" step="1000" min="0" name="amount" id="amount" placeholder="VD: 500000" 
               value="<?= $editPayment['amount'] ?? '' ?>" required>

        <label for="payment_method">Phương thức thanh toán:</label>
        <input type="text" name="payment_method" id="payment_method" value="<?= $editPayment['payment_method'] ?? '' ?>" required>

        <label for="status">Trạng thái:</label>
        <select name="status" id="status" required>
            <?php
            $statuses = ['pending' => 'Chờ xử lý', 'completed' => 'Hoàn thành', 'failed' => 'Thất bại'];
            foreach ($statuses as $key => $label) {
                $selected = isset($editPayment) && $editPayment['status'] == $key ? 'selected' : '';
                echo "<option value='$key' $selected>$label</option>";
            }
            ?>
        </select>

        <input type="submit" name="<?= $editPayment ? 'update' : 'add' ?>" 
               value="<?= $editPayment ? 'Cập nhật' : 'Thêm mới' ?>">
    </form>

    <h2>📋 Danh sách thanh toán</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên bệnh nhân</th>
                <th>Số tiền</th>
                <th>Ngày thanh toán</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['payment_id'] ?></td>
                <td><?= htmlspecialchars($row['patient_name']) ?></td>
                <td><?= number_format($row['amount'], 0, ',', '.') ?> VND</td>
                <td><?= $row['payment_date'] ?></td>
                <td class="status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></td>
                <td class="actions">
                    <a href="?edit=<?= $row['payment_id'] ?>">✏️ Sửa</a>
                    <a href="?delete=<?= $row['payment_id'] ?>" onclick="return confirm('Bạn chắc chắn muốn xoá thanh toán này?')">🗑️ Xoá</a>
                </td>
            </tr>
        <?php endwhile; ?>
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
