<?php
session_start();

// Hủy toàn bộ session hiện tại
session_unset();
session_destroy();

// Chuyển về trang đăng nhập
header("Location: trang_chu1.php");
exit();
?>
