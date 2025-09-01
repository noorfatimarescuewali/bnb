<?php
require 'db.php';

$loc = isset($_GET['loc']) ? trim($_GET['loc']) : '';
$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 1;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'price';

$params = [];
$sql = "SELECT id, title, description, price, location, images, rating, max_guests FROM properties WHERE 1=1";

if ($loc !== '') {
    $sql .= " AND location LIKE ?";
    $params[] = "%" . $loc . "%";
}
if ($guests > 1) {
    $sql .= " AND max_guests >= ?";
    $params[] = $guests;
}

if ($sort === 'price') {
    $sql .= " ORDER BY price ASC";
} elseif ($sort === 'rating') {
    $sql .= " ORDER BY rating DESC";
} else {
    $sql .= " ORDER BY id DESC";
}

$stmt = $mysqli->prepare($sql);
if ($params) {
    // build types
    $types = '';
    foreach ($params as $p) {
        if (is_int($p)) $types .= 'i'; else $types .= 's';
    }
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>AirClone — Listings</title>
<style>
/* Internal CSS */
body{font-family:Inter,Arial,sans-serif;background:#f6f8fb;margin:0;color:#222}
.container{max-width:1100px;margin:24px auto;padding:0 16px}
.header{display:flex;justify-content:space-between;align-items:center}
.search-summary{background:#fff;padding:12px;border-radius:10px;box-shadow:0 6px 18px rgba(10,20,50,0.06);margin-top:12px}
.list-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;margin-top:16px}
.card{background:#fff;border-radius:10px;box-shadow:0 6px 18px rgba(10,20,50,0.06);overflow:hidden;cursor:pointer}
.card img{width:100%;height:180px;object-fit:cover}
.meta{padding:12px}
.small{color:#666;font-size:13px}
.controls{display:flex;gap:8px;align-items:center}
.btn{padding:8px 12px;border-radius:8px;border:none;cursor:pointer}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <h2>Search results</h2>
    <div class="controls">
      <label class="small">Sort:</label>
      <select id="sort" onchange="applySort()">
        <option value="price" <?= $sort==='price' ? 'selected':'' ?>>Price (low → high)</option>
        <option value="rating" <?= $sort==='rating' ? 'selected':'' ?>>Top rated</option>
      </select>
      <button class="btn" onclick="goHome()">Home</button>
    </div>
  </div>

  <div class="search-summary">
    <strong>Location:</strong> <?= htmlspecialchars($loc ?: 'Anywhere') ?>
    <span style="margin-left:18px"><strong>Check-in:</strong> <?= htmlspecialchars($checkin ?: '—') ?></span>
    <span style="margin-left:18px"><strong>Check-out:</strong> <?= htmlspecialchars($checkout ?: '—') ?></span>
    <span style="float:right" class="small">Guests: <?= $guests ?></span>
  </div>

  <div class="list-grid">
    <?php while ($p = $res->fetch_assoc()) {
        $imgs = json_decode($p['images'], true);
        $img = $imgs[0] ?? '';
        $title = htmlspecialchars($p['title']);
        $desc  = htmlspecialchars(mb_strimwidth($p['description'],0,120,"..."));
        echo "<div class='card' onclick=\"window.location.href='property.php?id={$p['id']}&checkin={$checkin}&checkout={$checkout}&guests={$guests}'\">
                <img src='{$img}' alt=''>
                <div class='meta'>
                  <strong>{$title}</strong>
                  <div class='small'>{$p['location']} • \${$p['price']} / night <span style='float:right'>⭐ {$p['rating']}</span></div>
                  <p class='small' style='margin-top:8px'>{$desc}</p>
                </div>
              </div>";
    } ?>
  </div>

  <?php if ($res->num_rows == 0) : ?>
    <div style="margin-top:20px;background:#fff;padding:16px;border-radius:10px;box-shadow:0 6px 18px rgba(10,20,50,0.04)">No results — try a different location or adjust filters.</div>
  <?php endif; ?>

</div>

<script>
function applySort(){
  const params = new URLSearchParams(window.location.search);
  params.set('sort', document.getElementById('sort').value);
  window.location.href = 'listings.php?' + params.toString();
}
function goHome(){ window.location.href = 'index.php'; }
</script>
</body>
</html>
