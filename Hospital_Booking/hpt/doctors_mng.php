<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Thêm bác sĩ
if (isset($_POST['add_doctor'])) {
    $user_id = $_POST['user_id'];
    $specialty_id = $_POST['specialty_id'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO doctors (user_id, specialty_id, full_name, phone, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $user_id, $specialty_id, $full_name, $phone, $address);
    $stmt->execute();
}

// Sửa bác sĩ
if (isset($_POST['edit_doctor'])) {
    $doctor_id = $_POST['doctor_id'];
    $user_id = $_POST['user_id'];
    $specialty_id = $_POST['specialty_id'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("UPDATE doctors SET user_id=?, specialty_id=?, full_name=?, phone=?, address=? WHERE doctor_id=?");
    $stmt->bind_param("iisssi", $user_id, $specialty_id, $full_name, $phone, $address, $doctor_id);
    $stmt->execute();
}

// Xoá bác sĩ
if (isset($_GET['delete'])) {
    $doctor_id = $_GET['delete'];
    $conn->query("DELETE FROM doctors WHERE doctor_id=$doctor_id");
}

// Thêm chuyên khoa
if (isset($_POST['add_specialty'])) {
    $specialty_name = $_POST['specialty_name'];
    $stmt = $conn->prepare("INSERT INTO specialties (name) VALUES (?)");
    $stmt->bind_param("s", $specialty_name);
    $stmt->execute();
}

// Tìm kiếm bác sĩ
$search = $_GET['search'] ?? '';
$result = $conn->query("
    SELECT d.*, s.name AS specialty_name 
    FROM doctors d 
    LEFT JOIN specialties s ON d.specialty_id = s.specialty_id
    WHERE full_name LIKE '%$search%' OR phone LIKE '%$search%'
");

$specialties = $conn->query("SELECT * FROM specialties");
$users = $conn->query("SELECT * FROM users WHERE role='doctor' AND user_id NOT IN (SELECT user_id FROM doctors)");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Bác sĩ</title>
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

        label {
            margin-top: 10px;
            display: block;
            font-weight: 600;
        }

        input, select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 100%;
        }

        button {
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            margin-top: 15px;
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
        <a href="user_mng.php">QL Người Dùng</a>
        <a href="doctors_mng.php" class="active">QL Bác Sĩ</a>
        <a href="manage_specialties.php">QL Chuyên Khoa</a>
        <a href="appointment_mng.php">QL Lịch Khám</a>
        <a href="manage_payments.php">QL Thanh Toán</a>
        <a href="reports.php">Báo Cáo</a>
    </nav>
    <a href="#" onclick="confirmLogout(event)">Đăng xuất</a>

</header>

<div class="container">
    <h2>🩺 Quản lý Bác sĩ</h2>

    <form method="POST">
        <div class="form-row">
            <select name="user_id" required>
                <option value="">-- Chọn tài khoản --</option>
                <?php while ($row = $users->fetch_assoc()): ?>
                    <option value="<?= $row['user_id'] ?>"><?= $row['username'] ?></option>
                <?php endwhile; ?>
            </select>

            <input type="text" name="full_name" placeholder="Họ tên bác sĩ" required>
            <input type="text" name="phone" placeholder="Số điện thoại">
            <input type="text" name="address" placeholder="Địa chỉ">

            <select name="specialty_id" required>
                <option value="">-- Chuyên khoa --</option>
                <?php $specialties->data_seek(0); while ($s = $specialties->fetch_assoc()): ?>
                    <option value="<?= $s['specialty_id'] ?>"><?= $s['name'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" name="add_doctor" class="btn-primary">➕ Thêm bác sĩ</button>
    </form>

    <form method="POST">
        <input type="text" name="specialty_name" placeholder="Thêm chuyên khoa mới" required>
        <button type="submit" name="add_specialty" class="btn-primary">➕ Thêm chuyên khoa</button>
    </form>

    <form method="GET">
        <input type="text" name="search" placeholder="Tìm kiếm bác sĩ..." value="<?= htmlspecialchars($search) ?>" style="padding: 10px; width: 300px;">
        <button type="submit" class="btn-primary">🔍 Tìm</button>
    </form>

    <table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Ảnh</th>
            <th>Họ tên</th>
            <th>SĐT</th>
            <th>Địa chỉ</th>
            <th>Chuyên khoa</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['doctor_id'] ?></td>
            <td>
                <?php if (!empty($row['img'])): ?>
                    <img src="imgcss/<?= htmlspecialchars($row['img']) ?>" alt="Ảnh bác sĩ" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                <?php else: ?>
                    <span>Không có ảnh</span>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['full_name']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['address']) ?></td>
            <td><?= htmlspecialchars($row['specialty_name']) ?></td>
            <td>
                <a class="btn-warning" href="?edit=<?= $row['doctor_id'] ?>">Sửa</a>
                <a class="btn-danger" href="?delete=<?= $row['doctor_id'] ?>" onclick="return confirm('Xác nhận xoá?')">Xoá</a>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>

</table>


    <?php
    if (isset($_GET['edit'])):
        $id = $_GET['edit'];
        $edit = $conn->query("SELECT * FROM doctors WHERE doctor_id=$id")->fetch_assoc();
    ?>
    <form method="POST">
        <h2>🛠️ Sửa bác sĩ</h2>
        <input type="hidden" name="doctor_id" value="<?= $edit['doctor_id'] ?>">
        <div class="form-row">
            <input type="text" name="full_name" value="<?= $edit['full_name'] ?>" required>
            <input type="text" name="phone" value="<?= $edit['phone'] ?>">
            <input type="text" name="address" value="<?= $edit['address'] ?>">
            <select name="specialty_id" required>
                <?php $specialties->data_seek(0); while ($s = $specialties->fetch_assoc()): ?>
                    <option value="<?= $s['specialty_id'] ?>" <?= ($edit['specialty_id'] == $s['specialty_id']) ? 'selected' : '' ?>>
                        <?= $s['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="number" name="user_id" value="<?= $edit['user_id'] ?>" required>
        </div>
        <button type="submit" name="edit_doctor" class="btn-primary">✅ Cập nhật</button>
        <a href="doctors_mng.php" class="btn-danger" style="padding: 10px 16px; display: inline-block;">❌ Huỷ</a>
    </form>
    <?php endif; ?>

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
