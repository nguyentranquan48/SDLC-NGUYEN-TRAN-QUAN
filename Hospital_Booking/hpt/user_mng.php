<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$editMode = false;
$editUser = [
    "user_id" => "",
    "username" => "",
    "full_name" => "",
    "phone" => "",
    "address" => "",
    "role" => ""
];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_user"])) {
    if (!empty($_POST["role"])) {
        $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)");
        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt->bind_param("ssssss", $_POST['username'], $hashedPassword, $_POST['full_name'], $_POST['phone'], $_POST['address'], $_POST['role']);
        $stmt->execute();
        header("Location: user_mng.php");
        exit();
    } else {
        echo "<script>alert('Vui lòng chọn vai trò!');</script>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_user"])) {
    $stmt = $conn->prepare("UPDATE users SET username = ?, full_name = ?, phone = ?, address = ?, role = ? WHERE user_id = ?");
    $stmt->bind_param("sssssi", $_POST['username'], $_POST['full_name'], $_POST['phone'], $_POST['address'], $_POST['role'], $_POST['user_id']);
    $stmt->execute();
    header("Location: user_mng.php");
    exit();
}

if (isset($_GET["delete"])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_GET["delete"]);
    $stmt->execute();
    header("Location: user_mng.php");
    exit();
}

if (isset($_GET["edit"])) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_GET["edit"]);
    $stmt->execute();
    $result_edit = $stmt->get_result();
    if ($result_edit->num_rows > 0) {
        $editUser = $result_edit->fetch_assoc();
        $editMode = true;
    }
}

$search = $_GET["search"] ?? '';
$searchQuery = "%" . $search . "%";
$stmt = $conn->prepare("SELECT * FROM users WHERE CAST(user_id AS CHAR) LIKE ? OR username LIKE ? OR full_name LIKE ?");
$stmt->bind_param("sss", $searchQuery, $searchQuery, $searchQuery);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Người dùng</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to bottom right, #f0f8ff, #e6f7ff);
            color: #333;
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
        }
        .container {
            background: #fff;
            padding: 40px;
            margin: 30px auto;
            border-radius: 16px;
            box-shadow: 0 0 24px rgba(0,0,0,0.1);
            max-width: 1200px;
        }
        h2 {
            color: #005bbb;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        input, select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .btn {
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0069d9; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-warning:hover { background: #e0a800; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #f9f9f9;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ccc;
            text-align: center;
        }
        thead { background: #007bff; color: white; }
        tbody tr:hover { background: #eaf6ff; }
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
        <a href="user_mng.php" class="active">QL Người Dùng</a>
        <a href="doctors_mng.php">QL Bác Sĩ</a>
        <a href="manage_specialties.php">QL Chuyên Khoa</a>
        <a href="appointment_mng.php">QL Lịch Khám</a>
        <a href="manage_payments.php">QL Thanh Toán</a>
        <a href="reports.php">Báo Cáo</a>
    </nav>
    <a href="#" onclick="confirmLogout(event)">Đăng xuất</a>

</header>

<div class="container">
    <h2>👤 Quản lý Người dùng</h2>
    <form method="POST">
        <input type="hidden" name="user_id" value="<?= $editUser['user_id'] ?>">
        <div class="form-row">
            <input type="text" name="username" placeholder="Tên đăng nhập" value="<?= htmlspecialchars($editUser['username']) ?>" required>
            <?php if (!$editMode): ?>
                <input type="password" name="password" placeholder="Mật khẩu" required>
            <?php endif; ?>
            <input type="text" name="full_name" placeholder="Họ tên" value="<?= htmlspecialchars($editUser['full_name']) ?>" required>
            <input type="text" name="phone" placeholder="SĐT" value="<?= htmlspecialchars($editUser['phone']) ?>" required>
            <input type="text" name="address" placeholder="Địa chỉ" value="<?= htmlspecialchars($editUser['address']) ?>" required>
            <select name="role" required>
                <option value="">-- Vai trò --</option>
                <option value="admin" <?= $editUser['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="user" <?= $editUser['role'] == 'user' ? 'selected' : '' ?>>User</option>
            </select>
        </div>
        <button class="btn btn-primary" type="submit" name="<?= $editMode ? 'update_user' : 'add_user' ?>">
            <?= $editMode ? 'Cập nhật' : 'Thêm mới' ?>
        </button>
    </form>

    <form method="GET" style="margin-top: 30px;">
        <input type="text" name="search" placeholder="Tìm kiếm theo mã, tên..." value="<?= htmlspecialchars($search) ?>" style="width: 300px; padding: 10px;">
        <button type="submit" class="btn btn-primary">Tìm kiếm</button>
    </form>

    <table>
        <thead>
            <tr><th>ID</th><th>Tên đăng nhập</th><th>Họ tên</th><th>SĐT</th><th>Địa chỉ</th><th>Vai trò</th><th>Hành động</th></tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row["user_id"] ?></td>
                    <td><?= htmlspecialchars($row["username"]) ?></td>
                    <td><?= htmlspecialchars($row["full_name"]) ?></td>
                    <td><?= htmlspecialchars($row["phone"]) ?></td>
                    <td><?= htmlspecialchars($row["address"]) ?></td>
                    <td><?= htmlspecialchars($row["role"]) ?></td>
                    <td>
                        <a class="btn btn-warning btn-sm" href="?edit=<?= $row['user_id'] ?>">Sửa</a>
                        <a class="btn btn-danger btn-sm" href="?delete=<?= $row['user_id'] ?>" onclick="return confirm('Xác nhận xóa?')">Xóa</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
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