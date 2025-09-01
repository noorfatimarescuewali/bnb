<?php
// seed.php - run once to create tables and sample data
require 'db.php';

// Create properties table
$queries = [
"CREATE TABLE IF NOT EXISTS properties (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(8,2) NOT NULL,
  location VARCHAR(255),
  max_guests INT DEFAULT 2,
  images TEXT,
  rating DECIMAL(2,1) DEFAULT 5.0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

"CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  property_id INT NOT NULL,
  guest_name VARCHAR(255) NOT NULL,
  guest_email VARCHAR(255) NOT NULL,
  checkin DATE NOT NULL,
  checkout DATE NOT NULL,
  guests INT DEFAULT 1,
  total DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach ($queries as $q) {
    if (!$mysqli->query($q)) {
        echo "Error creating tables: " . $mysqli->error;
        exit;
    }
}

// Insert sample properties if none exist
$res = $mysqli->query("SELECT COUNT(*) as c FROM properties");
$row = $res->fetch_assoc();
if ($row['c'] == 0) {
    $props = [
        [
            'title' => 'Sunny City Loft',
            'description' => 'Bright loft near downtown, 1BR, great view, fast wifi.',
            'price' => 45.00,
            'location' => 'Karachi, Pakistan',
            'max_guests' => 3,
            'images' => json_encode(['data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"800\" height=\"500\"><rect fill=\"%23e1f5fe\" width=\"100%\" height=\"100%\"/><text x=\"50%\" y=\"50%\" font-size=\"36\" text-anchor=\"middle\" fill=\"%23333\">Sunny City Loft</text></svg>']),
            'rating' => 4.8
        ],
        [
            'title' => 'Cozy Studio by the Beach',
            'description' => 'Small studio, steps to the beach, perfect for couples.',
            'price' => 60.00,
            'location' => 'Clifton, Karachi',
            'max_guests' => 2,
            'images' => json_encode(['data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"800\" height=\"500\"><rect fill=\"%23fff9c4\" width=\"100%\" height=\"100%\"/><text x=\"50%\" y=\"50%\" font-size=\"36\" text-anchor=\"middle\" fill=\"%23333\">Cozy Studio</text></svg>']),
            'rating' => 4.9
        ],
        [
            'title' => 'Modern Family Home',
            'description' => '3BR modern home, great for families, full kitchen & garden.',
            'price' => 120.00,
            'location' => 'Gulshan-e-Iqbal, Karachi',
            'max_guests' => 6,
            'images' => json_encode(['data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"800\" height=\"500\"><rect fill=\"%23c8e6c9\" width=\"100%\" height=\"100%\"/><text x=\"50%\" y=\"50%\" font-size=\"36\" text-anchor=\"middle\" fill=\"%23333\">Modern Family Home</text></svg>']),
            'rating' => 4.7
        ],
    ];

    $stmt = $mysqli->prepare("INSERT INTO properties (title, description, price, location, max_guests, images, rating) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($props as $p) {
        $stmt->bind_param('ssdsi sd', $p['title'], $p['description'], $p['price'], $p['location'], $p['max_guests'], $p['images'], $p['rating']);
        // Note: binding spaces in types; PHP will ignore extra spaces - but to be safe use proper types
    }
    // Simpler: use direct insertion loop to avoid binding confusion
    foreach ($props as $p) {
        $title = $mysqli->real_escape_string($p['title']);
        $desc  = $mysqli->real_escape_string($p['description']);
        $price = $p['price'];
        $loc   = $mysqli->real_escape_string($p['location']);
        $maxg  = $p['max_guests'];
        $imgs  = $mysqli->real_escape_string($p['images']);
        $rating= $p['rating'];
        $mysqli->query("INSERT INTO properties (title, description, price, location, max_guests, images, rating) VALUES ('$title', '$desc', $price, '$loc', $maxg, '$imgs', $rating)");
    }

    echo "Sample properties inserted.<br>";
} else {
    echo "Properties already exist. No sample data inserted.<br>";
}

echo "Done. Remove or protect seed.php after running for security.";
?>
