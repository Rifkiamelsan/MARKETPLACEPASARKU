<?php
$items = [
  ["bawang-merah.jpg", "Bawang Merah Brebes", "#b5179e", "#f72585", "🧅", "Bumbu & Rempah"],
  ["cabai-rawit.jpg", "Cabai Rawit Segar", "#d90429", "#ff4d6d", "🌶️", "Sayur Segar"],
  ["beras-pandan.jpg", "Beras Pandan Wangi", "#2a9d8f", "#52b788", "🌾", "Sembako Pilihan"],
  ["gula-aren.jpg", "Gula Aren Batok", "#7f4f24", "#a68a64", "🪵", "Gula Alami"],
  ["tempe-daun.jpg", "Tempe Daun Pisang", "#38b000", "#70e000", "🍃", "Tradisional Asli"],
  ["lemper-ayam.jpg", "Lemper Bakar Ayam", "#bc6c25", "#dda15e", "🍙", "Jajanan Pasar"],
  ["wedang-uwuh.jpg", "Wedang Uwuh Imogiri", "#9b2226", "#d90429", "☕", "Minuman Rempah"],
  ["keranjang-bambu.jpg", "Tenggok Anyaman", "#936639", "#b08968", "🧺", "Kerajinan Tangan"],
  ["cobek-batu.jpg", "Cobek Batu Kali", "#495057", "#6c757d", "🪨", "Peralatan Dapur"],
  ["sayur-bayam.jpg", "Sayur Hijau Segar", "#2d6a4f", "#40916c", "🥬", "Sayuran Petani"]
];

$dir = __DIR__ . "/../assets/uploads/products/";
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

foreach ($items as $item) {
    list($fn, $title, $c1, $c2, $emoji, $cat) = $item;
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 450" width="100%" height="100%">
      <defs>
        <linearGradient id="grad_' . md5($fn) . '" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stop-color="' . $c1 . '" />
          <stop offset="100%" stop-color="' . $c2 . '" />
        </linearGradient>
      </defs>
      <rect width="600" height="450" fill="url(#grad_' . md5($fn) . ')" />
      <circle cx="300" cy="180" r="90" fill="#ffffff" opacity="0.25" />
      <text x="300" y="210" font-size="88" text-anchor="middle">' . $emoji . '</text>
      <rect x="40" y="320" width="520" height="96" rx="16" fill="#ffffff" opacity="0.95" />
      <text x="300" y="358" font-family="system-ui, -apple-system, sans-serif" font-size="22" font-weight="bold" fill="#212529" text-anchor="middle">' . $title . '</text>
      <text x="300" y="392" font-family="system-ui, -apple-system, sans-serif" font-size="14" font-weight="600" fill="' . $c1 . '" text-anchor="middle">✨ ' . $cat . '</text>
    </svg>';
    file_put_contents($dir . $fn, $svg);
}
echo "Sample product images created successfully!\n";
