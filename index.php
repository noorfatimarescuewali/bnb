<?php
require 'db.php';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>AirClone — Home</title>
<style>
/* Internal CSS - clean, modern, attractive */
*{box-sizing:border-box;font-family:Inter,Arial,Helvetica,sans-serif}
body{margin:0;background:#f6f8fb;color:#222}
.header{background:linear-gradient(135deg,#5567f2,#6ad3ff);padding:38px 20px;color:#fff;text-align:center}
.container{max-width:1100px;margin:20px auto;padding:0 16px}
.search-card{background:#fff;padding:18px;border-radius:12px;box-shadow:0 6px 22px rgba(10,20,50,0.08);display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.search-card input, .search-card select{padding:12px;border-radius:8px;border:1px solid #e6eefb;flex:1;min-width:150px}
.search-card button{background:#2b6ef6;color:#fff;border:none;padding:12px 18px;border-radius:8px;cursor:pointer;box-shadow:0 6px 18px rgba(43,110,246,0.18)}
.hero{margin-top:18px;text-align:left}
.features{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-top:20px}
.card{background:#fff;border-radius:10px;padding:14px;box-shadow:0 6px 18px rgba(12,20,40,0.06);cursor:pointer;overflow:hidden}
.card img{width:100%;height:160px;object-fit:cover;border-radius:8px 8px 0 0}
.card .meta{padding:10px}
.filters{display:flex;gap:8px;margin:12px 0;flex-wrap:wrap}
.small{font-size:13px;color:#555}
.footer{padding:22px;text-align:center;color:#777}
@media(max-width:600px){.search-card{padding:12px}}
</style>
</head>
<body>
<div class="header">
  <h1 style="margin:0;font-size:28px">AirClone</h1>
  <p style="margin:6px 0 0;opacity:0.95">Find great stays — fast, clean and simple.</p>
</div>

<div class="container">
  <div class="search-card" id="searchCard">
    <input id="q_loc" placeholder="Enter city or neighborhood (eg. Karachi)" />
    <input id="checkin" type="date" />
    <input id="checkout" type="date" />
    <select id="guests"><option value="1">1 guest</option><option value="2">2 guests</option><option value="3">3 guests</option><option value="4">4 guests</option></select>
    <button onclick="doSearch()">Search</button>
  </div>

  <div class="hero">
    <h2 style="margin:14px 0 6px">Featured stays</h2>
    <div class="filters small">Filters: <span style="margin-left:8px">Price • Type • Amenities (try search to see filters)</span></div>

    <div class="features" id="featured">
      <?php
      $res = $mysqli->query("SELECT id, title, price, location, images, rating FROM properties ORDER BY rating DESC LIMIT 3");
      while ($r = $res->fetch_assoc()) {
          $imgs = json_decode($r['images'], true);
          $img = isset($imgs[0]) ? $imgs[0] : '';
          $title = htmlspecialchars($r['title']);
          $loc = htmlspecialchars($r['location']);
          echo "<div class='card' onclick=\"goToProperty({$r['id']})\">
                  <img src='{$img}' alt=''>
                  <div class='meta'>
                    <strong>{$title}</strong>
                    <div class='small'>{$loc} • \${$r['price']} / night <span style='float:right'>⭐ {$r['rating']}</span></div>
                  </div>
                </div>";
      }
      ?>
    </div>
  </div>

  <div style="margin-top:22px">
    <h3 style="margin-bottom:8px">Quick links</h3>
    <div class="small">
      <a href="#" onclick="document.getElementById('q_loc').value='Karachi'; doSearch(); return false;">Karachi</a> •
      <a href="#" onclick="document.getElementById('q_loc').value='Clifton'; doSearch(); return false;">Clifton</a> •
      <a href="#" onclick="document.getElementById('q_loc').value='Gulshan-e-Iqbal'; doSearch(); return false;">Gulshan-e-Iqbal</a>
    </div>
  </div>

  <div class="footer">Made with ❤️ — AirClone demo</div>
</div>

<script>
function doSearch(){
  let loc = encodeURIComponent(document.getElementById('q_loc').value || '');
  let ci  = document.getElementById('checkin').value || '';
  let co  = document.getElementById('checkout').value || '';
  let guests = document.getElementById('guests').value;
  // Use JS redirection to listings.php with querystring
  let url = 'listings.php?loc='+loc+'&checkin='+ci+'&checkout='+co+'&guests='+guests;
  window.location.href = url;
}

function goToProperty(id){
  // js redirect to property page
  window.location.href = 'property.php?id=' + id;
}
</script>
</body>
</html>
