<?php

function a() {
    echo 'a';
    echo 'b';
    return;
    echo 'c';
}
function q()
{
    echo '###';
    die();
    die(12);
}
function qs()
{
    echo '###';
    if (true) {
        exit;
        echo '#';
    }
    throw new Exception();
    die(12);
}
function b()
{
    echo '###';
    if (false) {
        exit;
        echo '#';
    }
    throw new Exception();
    die(12);
}
echo 33;