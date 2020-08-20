<?php

function a()
{
    echo 'a';
    echo 'b';
    return;
}
function q()
{
    echo '###';
    die;
}
function qs()
{
    echo '###';
    exit;
}
function b()
{
    echo '###';
    throw new \Exception();
}
echo 33;