<?php
require_once '../config/db.php';
$pageTitle = 'FAQs';
include '../includes/header.php';
$faqs = [
  ['How long does film processing take?', 'Most processes take 2 to 7 days depending on the service, and the turnaround is shown on each service card. Developing is usually fastest. Scanning and printing add a few days, so a full package (develop, scan and print) can take up to a week.'],
  ['What film formats do you accept?', 'We process 35mm and 120 film, and can handle APS and other formats on request. Choose your format when you book so we can prepare the right equipment.'],
  ['How do I drop off my film?', 'Book a service on the Film Processing page and pick a drop-off date and time slot. Bring your rolls to the lab on that day with your QR code (printed or on your phone). We are open Monday to Friday, 10:00 AM to 5:00 PM.'],
  ['How do I pay?', 'We accept GoTyme, GCash, Maya and cash. For the first three, scan our QR code, pay the exact total, and attach a screenshot as proof of payment. We verify it and your status updates to Paid. Cash is paid at the lab.'],
  ['What are the service charge and VAT?', 'A 10% service charge and 12% VAT apply to film processing, repairs and store purchases. The total breakdown on every form shows these before you pay.'],
  ['How does the repair process work?', 'Choose up to two repair services, enter your camera details, and pick an available date. Drop off your camera on that day. We inspect it, record a diagnosis, and update the status as we go. You can follow every step in My Purchases.'],
  ['What does the QR code do?', 'Each booking, repair and order gets a QR code and a reference number. Our staff scan it at the lab to update your status, and you can print it from your receipt page.'],
  ['Can I cancel or get a refund?', 'You can cancel from My Purchases until we review your payment or start processing. Once we have reviewed or processed your service or product, it can no longer be cancelled and we cannot offer refunds.'],
  ['Do I need an account?', 'You can browse without one, but you need an account to book services, buy products, send repairs and register for events.'],
  ['Do you buy or trade cameras?', 'We host gear swaps and trading meetups. Check the Events page for the next one and register to join.'],
];
?>
<h1 class="page-title">Frequently Asked Questions</h1>
<?php foreach ($faqs as [$q, $a]): ?><details><summary><?= e($q) ?></summary><p><?= e($a) ?></p></details><?php endforeach; ?>
<p class="center" style="margin-top:1.5rem">Still have a question? <a class="btn" href="contact.php">Contact Us</a></p>
<?php include '../includes/footer.php'; ?>