<?php
require_once '../config/db.php';
$pageTitle = 'FAQs';
include '../includes/header.php';
$faqs = [
  ['How long does film processing take?', 'Most processes take 2 to 7 days depending on the service. The turnaround is shown on each service page. Rush processing moves your roll to the front of the queue.'],
  ['What film formats do you accept?', 'We process 35mm and 120 film, and we can handle APS and other formats on request. Choose the format when you book.'],
  ['How do I drop off my film?', 'Book a service on the Film Processing page, pick a drop-off date and time slot, and bring your rolls to the lab.'],
  ['How do I pay?', 'We accept GCash, Maya, cards and cash. GCash and Maya payments need a reference number. Cash is paid on site.'],
  ['How does the repair process work?', 'Submit a repair request, drop off your camera, and we inspect it for free. You can follow the status with your repair number on the Track Repair page.'],
  ['Can I cancel a booking?', 'Yes. You can cancel a booking from My Purchases while it is still Pending or Confirmed.'],
  ['Do you buy or trade cameras?', 'We host gear swaps and trading meetups. Check the Events page for the next one.'],
  ['How do I get my order?', 'Choose pickup at the lab or delivery at checkout. Track your order status in My Purchases.'],
];
?>
<h1>Frequently Asked Questions</h1>
<?php foreach ($faqs as [$q, $a]): ?>
  <details><summary><?= e($q) ?></summary><p><?= e($a) ?></p></details>
<?php endforeach; ?>
<p style="margin-top:1.5rem">Still have a question? <a class="btn" href="contact.php">Contact Us</a></p>
<?php include '../includes/footer.php'; ?>