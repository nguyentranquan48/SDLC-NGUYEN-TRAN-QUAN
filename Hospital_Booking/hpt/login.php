<?php
include 'connect.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Truy vấn kiểm tra người dùng
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Kiểm tra mật khẩu
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Điều hướng theo vai trò
            switch ($user['role']) {
                case 'admin':
                    header("Location: admin.php");
                    break;
                case 'doctor':
                    header("Location: doctor.php");
                    break;
                case 'patient':
                    header("Location: patient.php");
                    break;
                default:
                    $message = "Phân quyền không hợp lệ.";
            }
            exit();
        } else {
            $message = "Sai mật khẩu!";
        }
    } else {
        $message = "Tài khoản không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
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
        input {
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
        .register-link {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<form method="POST">
    <h2>Đăng nhập</h2>

    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

    <label>Tên đăng nhập:</label>
    <input type="text" name="username" required>

    <label>Mật khẩu:</label>
    <input type="password" name="password" required>

    <button type="submit">Đăng nhập</button>

    <div class="register-link">
        <p>Bạn chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
    </div>
</form>

</body>
</html>
