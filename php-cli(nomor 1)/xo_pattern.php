<?php
$size = 7;
for ($i = 0; $i < $size; $i++) {
    for ($j = 0; $j < $size; $j++) {
        echo ($j === $i || $j === $size - $i - 1) ? "X " : "O ";
    }
    echo PHP_EOL;
}
