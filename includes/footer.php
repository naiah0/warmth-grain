<?php $ftAdmin = in_admin_area() && is_admin(); ?>
</main>
<footer class="site-footer">
  <div class="footer-left">
    <strong>Warmth&amp;Grain</strong>
    <span>Email: <?= e(BIZ_EMAIL) ?></span>
    <span>Phone: <?= e(BIZ_PHONE) ?></span>
    <span>Hours: <?= e(BIZ_HOURS) ?></span>
    <span>Location: <?= e(BIZ_ADDRESS) ?></span>
  </div>
  <?php if (!$ftAdmin): ?><a class="btn" href="<?= BASE_URL ?>pages/contact.php">Contact Us</a><?php endif; ?>
  <p class="copy">&copy; <?= date('Y') ?> Warmth&amp;Grain. All rights reserved.</p>
</footer>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script src="<?= BASE_URL ?>assets/js/validate.js"></script>
<?php foreach (($scripts ?? []) as $s): ?>
<script src="<?= BASE_URL ?>assets/js/<?= e($s) ?>"></script>
<?php endforeach; ?>
</body>
</html>