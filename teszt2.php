<?php 

$month = 5;
$year = date("Y");
$sundays = 0;

for ($day = 1; $day <= date("t", strtotime("$year-$month-01")); $day++) {
    $date = strtotime("$year-$month-$day");
    if (date("w", $date) == 0) {
        $sundays++;
    }
}
echo "m: $month d: $sundays :y $year";