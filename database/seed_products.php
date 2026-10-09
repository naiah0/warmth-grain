<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if ($pdo->query('SELECT COUNT(*) FROM order_items')->fetchColumn() > 0) {
    die('Orders already exist, so seeding was stopped to protect your data.');
}
$pdo->exec('DELETE FROM products');
$pdo->exec('ALTER TABLE products AUTO_INCREMENT = 1');
$catId = $pdo->query('SELECT name, category_id FROM categories')->fetchAll(PDO::FETCH_KEY_PAIR);

$data = [
 'Cameras' => ['Capturing photos on film', 'Film cameras', [
  'Canon AE-1 Program|9500|Canon FD lenses; 35mm film','Olympus Trip 35|6500|35mm film (point-and-shoot)',
  'Pentax K1000|8500|K-mount lenses; 35mm film','Nikon FM2|14500|Nikon F-mount lenses; 35mm film',
  'Minolta X-700|7800|Minolta MD lenses; 35mm film','Yashica Electro 35 GSN|6800|35mm film (rangefinder)',
  'Canon AV-1|6200|Canon FD lenses; 35mm film','Olympus OM-1|8900|Olympus OM lenses; 35mm film',
  'Pentax Spotmatic|7200|M42 lenses; 35mm film','Konica C35|3800|35mm film (compact)',
  'Canon Canonet QL17 GIII|8200|35mm film (rangefinder)','Rollei 35 S|18500|35mm film (pocket camera)',
  'Mamiya C330|22000|120 film (twin-lens reflex)','Yashica Mat-124G|17500|120 film (twin-lens reflex)',
  'Kodak Retina IIa|5400|35mm film (folding camera)','Nikon F3|21000|Nikon F-mount lenses; 35mm film',
  'Olympus mju-II|15500|35mm film (compact)','Minolta Hi-Matic 7|4600|35mm film (rangefinder)',
  'Polaroid SX-70|12000|Polaroid SX-70 / i-Type film','Holga 120N|2800|120 film (toy camera)']],
 'Film' => ['Loading into a film camera to shoot', 'Film cameras', [
  'Kodak Gold 200|650|35mm cameras; C-41 color','Kodak ColorPlus 200|550|35mm cameras; C-41 color',
  'Kodak Ultramax 400|700|35mm cameras; C-41 color','Kodak Portra 160|1450|35mm cameras; C-41 color',
  'Kodak Portra 400|1550|35mm cameras; C-41 color','Kodak Portra 800|1750|35mm cameras; C-41 color',
  'Kodak Ektar 100|1350|35mm cameras; C-41 color','Kodak Tri-X 400|850|35mm cameras; black and white',
  'Kodak T-Max 100|900|35mm cameras; black and white','Kodak T-Max 400|900|35mm cameras; black and white',
  'Ilford HP5 Plus 400|700|35mm cameras; black and white','Ilford FP4 Plus 125|700|35mm cameras; black and white',
  'Ilford Delta 100|850|35mm cameras; black and white','Ilford Delta 3200|950|35mm cameras; black and white',
  'Fujifilm Superia X-tra 400|800|35mm cameras; C-41 color','Fujifilm C200|600|35mm cameras; C-41 color',
  'CineStill 800T|1650|35mm cameras; C-41 color','CineStill 50D|1600|35mm cameras; C-41 color',
  'Lomography Color Negative 400|600|35mm cameras; C-41 color','Kodak Ektachrome E100|1900|35mm cameras; E-6 slide']],
 'Analog Gear' => ['Developing, printing and handling film', 'Film shooters and darkroom users', [
  'Handheld Light Meter (Vintage)|1800|Any film camera','Compact Travel Tripod|2200|Cameras with a 1/4 inch mount',
  'Film Changing Bag|1100|Loading developing tanks','2-Reel Developing Tank|1600|35mm and 120 film',
  'Film Retriever|250|35mm cassettes','Archival Negative Sleeves (100)|650|35mm and 120 negatives',
  'Film Squeegee|300|Drying film after developing','Darkroom Thermometer|450|Controlling chemistry temperature',
  'C-41 Developing Kit|2200|C-41 color negative film','B&W Developer 500ml|750|Black and white film',
  'Rapid Fixer 1L|550|B&W film and paper','Stop Bath 500ml|350|Black and white developing',
  'Film Drying Clips (4)|280|Hanging film to dry','LED Light Table|2400|Viewing negatives and slides',
  'Negative Scanning Holder|900|Home flatbed scanners','8x Photographers Loupe|650|Checking negatives and sheets',
  'Contact Printing Frame|1200|Making contact sheets','Darkroom Timer|1400|Printing and developing',
  'Photo Paper 8x10 (25 sheets)|1300|Darkroom enlargers','Anti-Static Brush|300|Cleaning negatives and prints']],
 'Accessories' => ['Protecting, carrying and maintaining your camera', 'Most film cameras', [
  'Leather Camera Strap|900|Most 35mm and 120 cameras','Hard Camera Case|1800|SLR camera bodies',
  'Lens Cap 49mm|180|49mm filter thread lenses','Lens Cap 52mm|200|52mm filter thread lenses',
  'UV Filter 52mm|650|52mm filter thread lenses','Metal Lens Hood|450|50mm standard lenses',
  'Lens Cleaning Kit|550|Lenses and viewfinders','Rocket Air Blower|350|Lenses, bodies and negatives',
  'Microfiber Cloth|120|Lenses, screens and filters','Silica Gel Packs (10)|180|Camera bags and storage boxes',
  '35mm Film Canister Case|350|6 rolls of 35mm film','Padded Film Pouch|420|Up to 10 rolls of film',
  'LR44 Batteries (4 pcs)|180|Light meters and compacts','PX625 Battery Adapter|480|Cameras that need PX625 cells',
  'Mechanical Cable Release|380|Cameras with a threaded shutter button','Hot Shoe Cover|90|SLR and rangefinder cameras',
  'Nikon F Body Cap|180|Nikon F-mount bodies','M42 Body Cap|150|M42 screw-mount bodies',
  'Rubber Eyecup|220|Most SLR viewfinders','Camera Bag Divider Set|520|Camera bags and backpacks']],
 'Merchandise' => ['Showing your love for Warmth&Grain', 'Fans of analog photography', [
  'W&G Tote Bag|450','W&G Logo T-Shirt|750','W&G Dad Cap|600','W&G Enamel Pin|220','Sticker Pack (5 pcs)|150',
  'Film Postcard Set (10)|280','Ceramic Mug|380','A3 Art Poster|450','Dotted Notebook|320','Film Canister Keychain|180',
  'Photo Album (100 photos)|950','W&G Zine Issue 01|250','Embroidered Camera Strap|850','W&G Hoodie|1450',
  'Insulated Water Bottle|700','Large Canvas Tote|650','Film Strip Wall Clock|900','Polaroid-Style Photo Frame|400',
  'Lens Cap Keychain|200','Film Roll Candle|480']],
];

$ins = $pdo->prepare('INSERT INTO products (category_id,name,description,purpose,applicable_to,price,stock,image) VALUES (?,?,?,?,?,?,?,?)');
$n = 0;
foreach ($data as $cat => [$purpose, $defaultFor, $items]) {
    foreach ($items as $line) {
        $parts = explode('|', $line);
        $name = $parts[0]; $price = $parts[1]; $for = $parts[2] ?? $defaultFor;
        $desc = "$name from the Warmth&Grain curated selection. Checked and ready to use.";
        $stock = $cat === 'Cameras' ? mt_rand(1, 5) : mt_rand(5, 40);
        $ins->execute([$catId[$cat], $name, $desc, $purpose, $for, $price, $stock, slug($name) . '.jpg']);
        $n++;
    }
}
echo "Done: $n products added. Delete this file now.";