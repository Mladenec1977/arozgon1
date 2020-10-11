<?php
$file = 'counter.txt';
if (!file_get_contents($file)) {
    file_put_contents($file, 0);
}
$file_start = file_get_contents($file);
$start = $file_start + 1;
echo "User - " . $start;
file_put_contents($file, $start);
?>