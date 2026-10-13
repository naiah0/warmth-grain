<?php
require_once '../config/db.php';
$pageTitle = 'About'; $hasBanner = true;
include '../includes/header.php';
echo banner('About Warmth&Grain', 'One lab for your film, your camera and your community.', ['banners/about']);
$photos = array_filter(array_map(fn($i) => find_img("about/about-$i"), [1, 2, 3]));
?>
<div class="about">
  <h2>Our Story</h2>
  <div class="box">
    <p style="margin-bottom: 10px;"> More Than a Film Lab </p>
    <p>Warmth&Grain was created to be more than a place where customers drop off their film. We want it to be a space where people can process their photographs, discover analog equipment, repair cameras that still have more stories to tell, and connect with others who enjoy the same craft.
        From the first click of the shutter to the final developed negative, we aim to make the analog experience easier to access while still keeping the feeling that makes film photography special.</p>
  <p>So we built one lab that does it all. We develop, scan and print your film, repair the cameras that shot it, and run a small shop for the gear and film you need to keep going. Around the lab a community grew, and our gear swaps and trading meetups give old cameras a second life.</p></div>
  <?php if ($photos): ?><div class="about-photos"><?php foreach ($photos as $p): ?><img src="<?= e(BASE_URL . 'assets/img/' . $p) ?>" alt="Warmth&Grain"><?php endforeach; ?></div><?php endif; ?>

  <h2>What We Do</h2>
  <div class="grid-2">
    <a class="card card-link" href="services.php"><h3>Film Processing</h3><p class="small">Developing, scanning and printing for color, black and white, slide and cine film.</p></a>
    <a class="card card-link" href="shop.php"><h3>The Store</h3><p class="small">Vintage cameras, film, analog gear, accessories and merchandise.</p></a>
    <a class="card card-link" href="repair.php"><h3>Camera Repair</h3><p class="small">Light seals, shutters, lenses and full service, with status updates.</p></a>
    <a class="card card-link" href="events.php"><h3>Community Events</h3><p class="small">Gear swaps, trading meetups and workshops hosted at the lab.</p></a>
  </div>

  <h2>Who We Serve</h2>
  <div class="box"><p>We serve film photography enthusiasts and hobbyists, professional film photographers, filmmakers shooting on cine stock, and retro-lovers who enjoy vintage cameras and gear. Whether you shoot one roll a year or one a week, you are welcome here.</p></div>

  <h2>Our Promise</h2>
  <div class="box"><p>Careful handling of every roll, honest advice on gear, and fair pricing. Your negatives are irreplaceable, and we treat them that way.</p></div>

  <h2>Visit Us</h2>
  <div class="location-box">
    <p><strong>Address:</strong> <?= e(BIZ_ADDRESS) ?></p>
    <p><strong>Hours:</strong> <?= e(BIZ_HOURS) ?></p>
    <p><strong>Phone:</strong> <?= e(BIZ_PHONE) ?> &nbsp;|&nbsp; <strong>Email:</strong> <?= e(BIZ_EMAIL) ?></p>
    <p class="small muted">Drop-offs and repairs are accepted during opening hours.</p>
  </div>
</div>
<?php include '../includes/footer.php'; ?>