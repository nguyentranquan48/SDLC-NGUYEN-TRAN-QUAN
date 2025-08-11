<?php
include 'connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $full_name = trim($_POST["full_name"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);
    $role = $_POST["role"];

    // Kiểm tra username đã tồn tại chưa
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "Tên đăng nhập đã tồn tại!";
    } else {
        // Mã hóa mật khẩu
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Thêm tài khoản vào DB
        $insert = $conn->prepare("INSERT INTO users (username, password, full_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("ssssss", $username, $hashedPassword, $full_name, $phone, $address, $role);

        if ($insert->execute()) {
            header("Location: login.php"); // Chuyển đến trang đăng nhập
            exit();
        } else {
            $message = "Đăng ký thất bại!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #e9f0f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
            color: #007bff;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        .message {
            color: red;
            text-align: center;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        .login-link a {
            color: #007bff;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<form method="POST">
    <h2>Đăng ký tài khoản</h2>

    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

    <label>Tên đăng nhập:</label>
    <input type="text" name="username" required>

    <label>Mật khẩu:</label>
    <input type="password" name="password" required>

    <label>Họ và tên:</label>
    <input type="text" name="full_name">

    <label>Số điện thoại:</label>
    <input type="text" name="phone">

    <label>Địa chỉ:</label>
    <input type="text" name="address">

    <label>Vai trò:</label>
    <select name="role" required>
        <option value="patient">Bệnh nhân</option>
    </select>


    <button type="submit">Đăng ký</button>

    <div class="login-link">
        Đã có tài khoản? <a href="login.php">Đăng nhập tại đây</a>
    </div>
</form>

</body>
</html>
