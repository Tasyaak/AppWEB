<?php
// $db = new PDO(
//     'mysql:host=localhost;dbname=u67400',
//     'u67400',
//     '9728892',
//     [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
// );

$db = new PDO(
    'mysql:host=localhost;dbname=u67400',
    'root',
    '',
    [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

function checkinput($str)
{
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES);
}

function checkSQL($str)
{
    return htmlspecialchars_decode($str, ENT_QUOTES);
}
?>