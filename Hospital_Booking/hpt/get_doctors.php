<?php
include 'connect.php';

if (isset($_GET['specialty_id'])) {
    $specialty_id = (int)$_GET['specialty_id'];

    $stmt = $conn->prepare("SELECT doctor_id, full_name FROM doctors WHERE specialty_id = ?");
    $stmt->bind_param("i", $specialty_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($doctors);
    exit();
}
?>
