<?php

if (true) {
    var_dumP('qq');
} else {
    include 'define.php';
}

if(false) {
    echo "aa";
}
echo true ? '80' : '90';
echo 98 ?: '90';
echo $_SERVER['###'] ?? '90';

if (abc() && false) {
    exit;
}
if (abc() || true) {
    some();
} else {
    more();
}
if (!!!false) {
    echo "hi";
}

$a = array_key_exists('as', $_SERVER) ? $_SERVER['as'] : null;

return !array_key_exists('as', $_SERVER) ? null : $_SERVER['as'];