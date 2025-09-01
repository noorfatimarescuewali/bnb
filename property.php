<?php
require 'db.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    echo "Invalid property id"; exit;
}
$stmt = $mysqli->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { echo "Property not found"; exit; }
$p = $res->fetch_assoc();
$imgs = json_decode($p['images'], true);
$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 1;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?=htmlspecialchars($p['title'])?> — AirClone</title>
<style>
body{font-family:Inter,Arial,sans-serif;background:#f5f7fb;margin:0;color:#222}
.container{max-width:1000px;margin:20px auto;padding:0 16px}
.gallery{display:flex;gap:12px}
.gallery-main{flex:2}
.gallery-thumbs{flex:1;display:flex;flex-direction:column;gap:8px}
.hero-img{width:100%;height:420px;object-fit:cover;border-radius:12px}
.thumb{height:110px;object-fit:cover;border-radius:8px;cursor:pointer}
.details{background:#fff;padding:14px;border-radius:10px;box-shadow:0 6px 18px rgba(10,20,50,0.06);margin-top:14px}
.book-form{background:#fff;padding:14px;border-radius:10px;box-shadow:0 6px 18px rgba(10,20,50,0.06);margin-top:14px}
.btn{background:#2b6ef6;color:#fff;padding:10px 14px;border:none;border-radius:8px;cursor:pointer}
.small{color:#666;font-size:13px}
</style>
</head>
<body>
<div class="container">
  <button onclick="goBack()" class="small">← Back</button>
  <div class="gallery" style="margin-top:8px">
    <div class="gallery-main">
      <img id="mainImg" class="hero-img" src="<?=htmlspecialchars($imgs[0] ?? '')?>" alt="">
    </div>
    <div class="gallery-thumbs">
      <?php foreach ($imgs as $im) {
        echo "<img class='thumb' src='".htmlspecialchars($im)."' onclick=\"document.getElementById('mainImg').src='".htmlspecialchars($im)."';\">";
      } ?>
    </div>
  </div>

  <div style="display:flex;gap:16px;flex-wrap:wrap">
    <div style="flex:1;min-width:320px">
      <div class="details">
        <h2><?=htmlspecialchars($p['title'])?></h2>
        <div class="small"><?=htmlspecialchars($p['location'])?> • ⭐ <?= $p['rating'] ?> • Max guests: <?= $p['max_guests'] ?></div>
        <p style="margin-top:10px"><?=nl2br(htmlspecialchars($p['description']))?></p>
        <p style="font-weight:700;margin-top:8px">Price: $<?= $p['price'] ?> / night</p>
      </div>
    </div>

    <div style="width:360px">
      <div class="book-form">
        <form id="bookingForm" onsubmit="submitBooking(event)">
          <input type="hidden" name="property_id" value="<?= $p['id'] ?>">
          <label class="small">Check-in</label><br>
          <input type="date" name="checkin" required value="<?=htmlspecialchars($checkin)?>" style="width:100%;padding:8px;margin-bottom:8px;border-radius:6px;border:1px solid #eaeef7">
          <label class="small">Check-out</label><br>
          <input type="date" name="checkout" required value="<?=htmlspecialchars($checkout)?>" style="width:100%;padding:8px;margin-bottom:8px;border-radius:6px;border:1px solid #eaeef7">
          <label class="small">Guests</label><br>
          <input type="number" name="guests" min="1" max="<?= $p['max_guests'] ?>" value="<?= $guests ?>" style="width:100%;padding:8px;margin-bottom:8px;border-radius:6px;border:1px solid #eaeef7">
          <label class="small">Your name</label><br>
          <input type="text" name="guest_name" required style="width:100%;padding:8px;margin-bottom:8px;border-radius:6px;border:1px solid #eaeef7">
          <label class="small">Email</label><br>
          <input type="email" name="guest_email" required style="width:100%;padding:8px;margin-bottom:12px;border-radius:6px;border:1px solid #eaeef7">

          <div style="display:flex;justify-content:space-between;align-items:center">
            <div>
              <div class="small">Total estimate</div>
              <div style="font-weight:700">$<span id="totalPrice">0.00</span></div>
            </div>
            <button type="submit" class="btn">Book now</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>

<script>
const pricePerNight = <?= floatval($p['price']) ?>;
function goBack(){ window.history.back(); }

// Calculate nights and price
function calcTotal(){
  const f = document.getElementById('bookingForm');
  const ci = f.checkin.value;
  const co = f.checkout.value;
  if (!ci || !co) { document.getElementById('totalPrice').innerText = '0.00'; return; }
  const d1 = new Date(ci);
  const d2 = new Date(co);
  const diff = (d2 - d1) / (1000*60*60*24);
  const nights = diff > 0 ? diff : 0;
  const total = (nights * pricePerNight) || 0;
  document.getElementById('totalPrice').innerText = total.toFixed(2);
}

document.getElementById('bookingForm').addEventListener('input', calcTotal);
calcTotal();

function submitBooking(e){
  e.preventDefault();
  const form = new FormData(e.target);
  // send to book.php via fetch and then JS redirect on success
  fetch('book.php', { method:'POST', body: form })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        // Redirect to confirmation page with booking id
        window.location.href = 'confirm.php?id=' + data.booking_id;
      } else {
        alert('Booking failed: ' + (data.error || 'unknown error'));
      }
    })
    .catch(err => alert('Network error: ' + err));
}
</script>
</body>
</html>
