  </main>
</div>
<footer class="site-footer">
  <p>&copy; <?= date('Y') ?> Warmth&amp;Grain. All rights reserved.</p>
</footer>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script src="<?= BASE_URL ?>assets/js/validate.js"></script>
<?php foreach (($scripts ?? []) as $s): ?>
<script src="<?= BASE_URL ?>assets/js/<?= e($s) ?>"></script>
<?php endforeach; ?>
</body>
</html>