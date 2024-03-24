<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="bootstrap.min.css" />
    <title>Zadacha 3</title>
  </head>
  
  <body>

<?php

header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  if (!empty($_GET['save'])) {

    print('Спасибо, результаты сохранены.');
  }
  include('form.php');
  exit();
}




function errp($error){
  print("<div class='messageError'>$error</div>");
  exit();
}

function val_empty($val, $fio, $o = 0){
  if(empty($val)){
    if($o == 0){
      errp("Заполните поле $fio.<br/>");
    }
    if($o == 1){
      errp("Выберите $fio.<br/>");
    }
    if($o == 2){
      errp("ознакомьтесь с контрактом<br/>");
    }
    exit();
  }
}

$errors = '';
$fio = (isset($_POST['fio']) ? $_POST['fio'] : '');
$number= (isset($_POST['number']) ? $_POST['number'] : '');
$email = (isset($_POST['email']) ? $_POST['email'] : '');
$date = (isset($_POST['date']) ? strtotime($_POST['date']) : '');
$radio = (isset($_POST['radio']) ? $_POST['radio'] : '');
$language = (isset($_POST['language']) ? $_POST['language'] : '');
$biography = (isset($_POST['biography']) ? $_POST['biography'] : '');
$check = (isset($_POST['check']) ? $_POST['check'] : '');


$number = preg_replace('/\D/', '', $number);
  
$languages = ($language != '') ? implode(", ", $language) : [];

val_empty($fio, "имя");
val_empty($number, "телефон");
val_empty($email, "email");
val_empty($date, "дата");
val_empty($radio, "пол", 1);
val_empty($language, "языки", 1);
val_empty($biography, "биографию");
val_empty($check, "ознакомлен", 2);
if(empty($fio)){
  print('пустое поле фио');
}

if(strlen($fio) > 255){
  $errors = 'Длина поля "ФИО" > 255 символов';
}
elseif(count(explode(" ", $fio)) < 2){
  $errors = 'Неверный формат ФИО';
} 
elseif(strlen($number) != 11){
  $errors = 'Неверное значение поля "Телефон"';
}
elseif(strlen($number) > 255){
  $errors = 'Длина поля "email" > 255 символов';
}
elseif(!preg_match('/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/', $email)){
  $errors = 'Неверное значение поля "email"';
}
elseif(!is_numeric($date) || strtotime("now") < $date){
  $errors = 'Укажите корректно дату';
}
elseif($radio != "M" && $radio != "W"){
  $errors = 'Укажите пол';
}
elseif(count($language) == 0){
  $errors = 'Укажите языки';
}

if ($errors != '') {
  errp($errors);
}

$db = new PDO('mysql:host=localhost;dbname=u67400', 'u67400', '9728892',
   [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$inQuery = implode(',', array_fill(0, count($language), '?'));


try {
  $dbLangs = $db->prepare("SELECT id, name FROM languages WHERE name IN ($inQuery)");
  foreach ($language as $key => $value) {
    $dbLangs->bindValue(($key+1), $value);
  }
  $dbLangs->execute();
  $languages = $dbLangs->fetchAll(PDO::FETCH_ASSOC);
}
catch(PDOException $e){
  print('Error : ' . $e->getMessage());
  exit();
}

echo $dbLangs->rowCount().'**'.count($language);

if($dbLangs->rowCount() != count($language)){
  $errors = 'Неверно выбраны языки';
}
elseif(strlen($biography) > 65535){
  $errors = 'Длина поля "Биография" > 65 535 символов';
}

if ($errors != '') {
  errp($errors);
}

try {
  $stmt = $db->prepare("INSERT INTO form_data (fio, number, email, date, radio, biography) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->execute([$fio, $number, $email, $date, $radio, $biography]);
  $fid = $db->lastInsertId();
  $stmt1 = $db->prepare("INSERT INTO form_data_lang (id_form, id_lang) VALUES (?, ?)");
  foreach($languages as $row){
      $stmt1->execute([$fid, $row['id']]);
  }
}
catch(PDOException $e){
  print('Error : ' . $e->getMessage());
  exit();
}

header('Location: ?save=1');