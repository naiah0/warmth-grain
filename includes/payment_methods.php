<div data-payment>
  <label>Payment Method</label>
  <div class="pay-options">
    <?php foreach (['GCash' => 'GCash', 'Maya' => 'Maya', 'Card' => 'Debit / Credit Card', 'Cash' => 'Cash (pay on site)'] as $val => $label): ?>
      <label class="pay-option"><input type="radio" name="method" value="<?= e($val) ?>" <?= $val === 'GCash' ? 'checked' : '' ?>><?= e($label) ?></label>
    <?php endforeach; ?>
  </div>
  <p class="pay-info small muted"></p>
  <div class="pay-ref" style="display:none">
    <label for="reference_no">Payment Reference No.</label>
    <input type="text" id="reference_no" name="reference_no" maxlength="20" placeholder="e.g. 5012345678">
  </div>
</div>