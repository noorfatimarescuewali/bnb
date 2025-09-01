<?php
// book.php - handle booking insertion, return JSON {success: true, booking_id: N}
require 'db.php';
header('Content-Type: application/json');

$property_id = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;
$checkin = $_POST['checkin'] ?? '';
$checkout = $_POST['checkout'] ?? '';
$guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 1;
$guest_name = trim($_POST['guest_name'] ?? '');
$guest_email = trim($_POST['guest_email'] ?? '');

if (!$property_id || !$checkin || !$checkout || !$guest_name || !$guest_email) {
    echo json_encode(['success'=>false,'error'=>'Missing fields']);
    exit;
}

// validate dates
$ci = strtotime($checkin);
$co = strtotime($checkout);
if (!$ci || !$co || $co <= $ci) {
    echo json_encode(['success'=>false,'error'=>'Invalid check-in/check-out']);
    exit;
}

$nights = ($co - $ci)/(60*60*24);
if ($nights <= 0) {
    echo json_encode(['success'=>false,'error'=>'Check-out must be after check-in']);
    exit;
}

// get price/night
$stmt = $mysqli->prepare("SELECT price FROM properties WHERE id = ?");
$stmt->bind_param('i', $property_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['success'=>false,'error'=>'Property not found']);
    exit;
}
$price = $res->fetch_assoc()['price'];
$total = $price * $nights;

// insert booking
$ins = $mysqli->prepare("INSERT INTO bookings (property_id, guest_name, guest_email, checkin, checkout, guests, total) VALUES (?, ?, ?, ?, ?, ?, ?)");
$ins->bind_param('isssids', $property_id, $guest_name, $guest_email, $checkin, $checkout, $guests, $total);
if ($ins->execute()) {
    $booking_id = $ins->insert_id;
    // Optionally send email or message here (not implemented)
    echo json_encode(['success'=>true, 'booking_id'=>$booking_id]);
    exit;
} else {
    echo json_encode(['success'=>false,'error'=>$mysqli->error]);
    exit;
}
?>
