<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$search = $_GET['search'] ?? '';

// Thêm chuyên khoa
if (isset($_POST['add_specialty'])) {
    $specialty_name = $_POST['specialty_name'];
    if (!empty($specialty_name)) {
        $stmt = $conn->prepare("INSERT INTO specialties (name) VALUES (?)");
        $stmt->bind_param("s", $specialty_name);
        $stmt->execute();
    }
}

// Sửa chuyên khoa
if (isset($_POST['edit_specialty'])) {
    $id = $_POST['specialty_id'];
    $name = $_POST['specialty_name'];
    if (!empty($name)) {
        $stmt = $conn->prepare("UPDATE specialties SET name = ? WHERE specialty_id = ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
    }
}

// Xoá chuyên khoa
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM specialties WHERE specialty_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Lấy danh sách chuyên khoa (tìm kiếm)
$stmt = $conn->prepare("SELECT * FROM specialties WHERE name LIKE ? ORDER BY name ASC");
$like = "%$search%";
$stmt->bind_param("s", $like);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Chuyên Khoa</title>
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
            max-width: 800px;
        }

        h2 {
            color: #005bbb;
            margin-bottom: 20px;
            font-size: 24px;
        }

        input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 100%;
            margin-bottom: 20px;
            font-size: 16px;
        }

        button {
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

        form.inline-form {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: center;
        }

        form.inline-form input[type="text"] {
            width: auto;
            flex: 1;
            margin-bottom: 0;
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
        <a href="manage_specialties.php" class="active">QL Chuyên Khoa</a>
        <a href="appointment_mng.php">QL Lịch Khám</a>
        <a href="manage_payments.php">QL Thanh Toán</a>
        <a href="reports.php">Báo Cáo</a>
    </nav>
    <a href="#" onclick="confirmLogout(event)">Đăng xuất</a>

</header>

<div class="container">
    <h2>📋 Quản lý Chuyên Khoa</h2>

    <!-- Tìm kiếm -->
    <form method="GET" style="margin-bottom: 30px;">
        <input type="text" name="search" placeholder="Tìm chuyên khoa..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-primary">🔍 Tìm</button>
    </form>

    <!-- Thêm chuyên khoa -->
    <form method="POST" style="margin-bottom: 30px;">
        <input type="text" name="specialty_name" placeholder="Tên chuyên khoa mới" required>
        <button type="submit" name="add_specialty" class="btn-primary">➕ Thêm chuyên khoa</button>
    </form>

    <!-- Danh sách chuyên khoa -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên chuyên khoa</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['specialty_id'] ?></td>
                    <td>
                        <?php if (isset($_GET['edit']) && $_GET['edit'] == $row['specialty_id']): ?>
                            <!-- Form sửa chuyên khoa -->
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="specialty_id" value="<?= $row['specialty_id'] ?>">
                                <input type="text" name="specialty_name" value="<?= htmlspecialchars($row['name']) ?>" required>
                                <button type="submit" name="edit_specialty" class="btn-warning">💾 Lưu</button>
                                <a href="manage_specialties.php" class="btn-danger" style="padding: 10px 16px; display: inline-block; text-decoration:none;">❌ Huỷ</a>
                            </form>
                        <?php else: ?>
                            <?= htmlspecialchars($row['name']) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!isset($_GET['edit']) || $_GET['edit'] != $row['specialty_id']): ?>
                            <a href="?edit=<?= $row['specialty_id'] ?>" class="btn-warning">✏️ Sửa</a>
                            <a href="?delete=<?= $row['specialty_id'] ?>" class="btn-danger" onclick="return confirm('Bạn chắc chắn muốn xoá chuyên khoa này?')">🗑️ Xoá</a>
                        <?php endif; ?>
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
