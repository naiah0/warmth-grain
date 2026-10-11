<?php
require 'db.php';
foreach (['users','categories','products','services','packages','repair_services','events'] as $t)
    echo $t . ': ' . $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn() . '<br>';
