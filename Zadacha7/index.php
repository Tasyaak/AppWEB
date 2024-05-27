<?php

$db;
include ('database.php');
header("Content-Type: text/html; charset=UTF-8");
session_start();

if (strpos($_SERVER['REQUEST_URI'], 'index.php') === false) {
    header('Location: index.php');
    exit();
}

$error = false;
$log = isset($_SESSION['login']);
$adminLog = isset($_SERVER['PHP_AUTH_USER']);
$uid = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
$getUid = isset($_GET['uid']) ? checkinput($_GET['uid']) : '';

if ($adminLog && preg_match('/^[0-9]+$/', $getUid)) {
    $uid = $getUid;
    $log = true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    $fio = isset($_POST['fio']) ? checkinput($_POST['fio']) : '';
    $number = isset($_POST['number']) ? checkinput($_POST['number']) : '';
    $email = isset($_POST['email']) ? checkinput($_POST['email']) : '';
    $date = isset($_POST['date']) ? checkinput($_POST['date']) : '';
    $radio = isset($_POST['radio']) ? checkinput($_POST['radio']) : '';
    $language = isset($_POST['language']) ? checkinput($_POST['language']) : [];
    $bio = isset($_POST['bio']) ? checkinput($_POST['bio']) : '';
    $check = isset($_POST['check']) ? checkinput($_POST['check']) : '';

    if ($csrf_token != $_SESSION['csrf_token']) {
        setcookie('csrf_token', '1');
        header('Location: index.php' . (($getUid != NULL) ? '?uid=' . $uid : ''));
        exit();
    }

    if (isset($_POST['logout_form'])) {
        if ($adminLog && empty($_SESSION['login']))
            header('Location: admin.php');
        else {
            setcookie('fio_value', '', time() - 30 * 24 * 60 * 60);
            setcookie('number_value', '', time() - 30 * 24 * 60 * 60);
            setcookie('email_value', '', time() - 30 * 24 * 60 * 60);
            setcookie('date_value', '', time() - 30 * 24 * 60 * 60);
            setcookie('radio_value', '', time() - 30 * 24 * 60 * 60);
            setcookie('language_value', '', time() - 30 * 24 * 60 * 60);
            setcookie('bio_value', '', time() - 30 * 24 * 60 * 60);
            setcookie('check_value', '', time() - 30 * 24 * 60 * 60);
            session_destroy();
            header('Location: index.php' . (($getUid != NULL) ? '?uid=' . $uid : ''));
        }
        exit();
    }

    function check_pole($cook, $str, $flag)
    {
        global $error;
        $res = false;
        $setval = isset($_POST[$cook]) ? $_POST[$cook] : '';
        if ($flag) {
            setcookie($cook . '_error', $str, time() + 24 * 60 * 60);
            $error = true;
            $res = true;
        }
        if ($cook == 'language') {
            global $language;
            $setval = ($language != '') ? implode(",", $language) : '';
        }
        setcookie($cook . '_value', $setval, time() + 30 * 24 * 60 * 60);
        return $res;
    }

    if (!check_pole('fio', 'Это поле пустое', empty($fio)))
        check_pole('fio', 'Неправильный формат: Имя Фамилия (Отчество), только кириллица', !preg_match('/^([а-яё]+-?[а-яё]+)( [а-яё]+-?[а-яё]+){1,2}$/Diu', $fio));
    if (!check_pole('number', 'Это поле пустое', empty($number))) {
        check_pole('number', 'Неправильный формат, должно быть 11 символов', strlen($number) != 11);
        check_pole('number', 'Поле должно содержать только цифры', $number != preg_replace('/\D/', '', $number));
    }
    if (!check_pole('email', 'Это поле пустое', empty($email)))
        check_pole('email', 'Неправильный формат: example@mail.ru', !preg_match('/^\w+([.-]?\w+)@\w+([.-]?\w+)(.\w{2,3})+$/', $email));
    if (!check_pole('date', 'Это поле пустое', empty($date)))
        check_pole('date', 'Неправильная дата', strtotime('now') < strtotime($date));
    check_pole('radio', "Не выбран пол", empty($radio) || !preg_match('/^(M|W)$/', $radio));
    if (!check_pole('bio', 'Это поле пустое', empty($bio)))
        check_pole('bio', 'Слишком длинное поле, максимум символов - 65535', strlen($bio) > 65535);
    check_pole('check', 'Не ознакомлены с контрактом', empty($check));

    if (!check_pole('language', 'Не выбран язык', empty($language))) {
        try {
            $inQuery = implode(',', array_fill(0, count($language), '?'));
            $dbLangs = $db->prepare("SELECT id, name FROM languages WHERE name IN ($inQuery)");
            foreach ($language as $key => $value)
                $dbLangs->bindValue(($key + 1), checkinput($value));
            $dbLangs->execute();
            $languages = $dbLangs->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            print ('Error : ' . $e->getMessage());
            exit();
        }
        check_pole('language', 'Неверно выбраны языки', $dbLangs->rowCount() != count($language));
    }

    if (!$error) {
        setcookie('fio_error', '', time() - 30 * 24 * 60 * 60);
        setcookie('number_error', '', time() - 30 * 24 * 60 * 60);
        setcookie('email_error', '', time() - 30 * 24 * 60 * 60);
        setcookie('date_error', '', time() - 30 * 24 * 60 * 60);
        setcookie('radio_error', '', time() - 30 * 24 * 60 * 60);
        setcookie('language_error', '', time() - 30 * 24 * 60 * 60);
        setcookie('bio_error', '', time() - 30 * 24 * 60 * 60);
        setcookie('check_error', '', time() - 30 * 24 * 60 * 60);

        if ($log) {
            $stmt = $db->prepare("UPDATE form_data SET fio = ?, number = ?, email = ?, date = ?, radio = ?, bio = ? WHERE user_id = ?");
            $stmt->execute([$fio, $number, $email, strtotime($date), $radio, $bio, $_SESSION['user_id']]);

            $stmt = $db->prepare("DELETE FROM form_data_lang WHERE id_form = ?");
            $stmt->execute([$_SESSION['form_id']]);

            $stmt1 = $db->prepare("INSERT INTO form_data_lang (id_form, id_lang) VALUES (?, ?)");
            foreach ($languages as $row)
                $stmt1->execute([$_SESSION['form_id'], $row['id']]);
            if ($adminLog)
                setcookie('admin_value', '1', time() + 30 * 24 * 60 * 60);
        } else {
            $login = uniqid();
            $pass = uniqid();
            setcookie('login', $login);
            setcookie('pass', $pass);
            $mpass = md5($pass);
            try {
                $stmt = $db->prepare("INSERT INTO users (login, password) VALUES (?, ?)");
                $stmt->execute([$login, $mpass]);
                $user_id = $db->lastInsertId();

                $stmt = $db->prepare("INSERT INTO form_data (user_id, fio, number, email, date, radio, bio) VALUES (?, ?, ?, ?, ?, ?, ? )");
                $stmt->execute([$user_id, $fio, $number, $email, strtotime($date), $radio, $bio]);
                $fid = $db->lastInsertId();

                $stmt1 = $db->prepare("INSERT INTO form_data_lang (id_form, id_lang) VALUES (?, ?)");
                foreach ($languages as $row)
                    $stmt1->execute([$fid, $row['id']]);
            } catch (PDOException $e) {
                print ('Error : ' . $e->getMessage());
                exit();
            }
            setcookie('fio_value', $fio, time() + 24 * 60 * 60 * 365);
            setcookie('number_value', $number, time() + 24 * 60 * 60 * 365);
            setcookie('email_value', $email, time() + 24 * 60 * 60 * 365);
            setcookie('date_value', $date, time() + 24 * 60 * 60 * 365);
            setcookie('radio_value', $radio, time() + 24 * 60 * 60 * 365);
            setcookie('language_value', implode(",", $language), time() + 24 * 60 * 60 * 365);
            setcookie('bio_value', $bio, time() + 24 * 60 * 60 * 365);
            setcookie('check_value', $check, time() + 24 * 60 * 60 * 365);
        }
        setcookie('save', '1');
    }
    header('Location: index.php' . (($getUid != NULL) ? '?uid=' . $uid : ''));
} else {
    if (($adminLog && isset($getUid)) || !$adminLog) {
        $cookAdmin = (isset($_COOKIE['admin_value']) ? $_COOKIE['admin_value'] : '');
        if ($cookAdmin == '1') {
            setcookie('fio_value', '', time() - 30 * 24 * 60 * 60);
            setcookie('number_value', '', time() - 30 * 24 * 60 * 60);
            setcookie('email_value', '', time() - 30 * 24 * 60 * 60);
            setcookie('date_value', '', time() - 30 * 24 * 60 * 60);
            setcookie('radio_value', '', time() - 30 * 24 * 60 * 60);
            setcookie('language_value', '', time() - 30 * 24 * 60 * 60);
            setcookie('bio_value', '', time() - 30 * 24 * 60 * 60);
            setcookie('check_value', '', time() - 30 * 24 * 60 * 60);
        }
    }

    $csrf_error = isset($_COOKIE['csrf_error']) ? checkinput($_COOKIE['csrf_error']) : '';
    $fio = isset($_COOKIE['fio_error']) ? checkinput($_COOKIE['fio_error']) : '';
    $number = isset($_COOKIE['number_error']) ? checkinput($_COOKIE['number_error']) : '';
    $email = isset($_COOKIE['email_error']) ? checkinput($_COOKIE['email_error']) : '';
    $date = isset($_COOKIE['date_error']) ? checkinput($_COOKIE['date_error']) : '';
    $radio = isset($_COOKIE['radio_error']) ? checkinput($_COOKIE['radio_error']) : '';
    $language = isset($_COOKIE['language_error']) ? checkinput($_COOKIE['language_error']) : '';
    $bio = isset($_COOKIE['bio_error']) ? checkinput($_COOKIE['bio_error']) : '';
    $check = isset($_COOKIE['check_error']) ? checkinput($_COOKIE['check_error']) : '';

    $errors = array();
    $messages = array();
    $values = array();
    $error = true;

    function set_val($str, $pole)
    {
        global $values;
        $values[$str] = empty($pole) ? '' : checkinput($pole);
    }

    function check_pole($str, $pole)
    {
        global $errors, $messages, $values, $error;
        if ($error)
            $error = empty($_COOKIE[$str . '_error']);
        $errors[$str] = isset($_COOKIE[$str . '_error']);
        $messages[$str] = "<div class=\"error\">$pole</div>";
        $values[$str] = empty($_COOKIE[$str . '_value']) ? '' : checkinput($_COOKIE[$str . '_value']);
        setcookie($str . '_error', '', time() - 30 * 24 * 60 * 60);
        return;
    }

    if (isset($_COOKIE['csrf_error']))
        setcookie('csrf_error', '', 100000);
    if (isset($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        setcookie('login', '', 100000);
        setcookie('pass', '', 100000);
        $messages['success'] = 'Спасибо, результаты сохранены.';
        if (isset($_COOKIE['pass']))
            $messages['info'] = sprintf('Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong><br>
            и паролем <strong>%s</strong> для изменения данных.', checkinput($_COOKIE['login']), checkinput($_COOKIE['pass']));
    }

    check_pole('fio', $fio);
    check_pole('number', $number);
    check_pole('email', $email);
    check_pole('date', $date);
    check_pole('radio', $radio);
    check_pole('language', $language);
    check_pole('bio', $bio);
    check_pole('check', $check);

    $languages = explode(',', $values['language']);

    if ($error && $log) {
        try {
            $dbLangs = $db->prepare("SELECT * FROM form_data WHERE user_id = ?");
            $dbLangs->execute([$uid]);
            $nichego = $dbLangs->fetchAll(PDO::FETCH_ASSOC)[0];

            $form_id = $nichego['id'];
            $_SESSION['form_id'] = $form_id;

            $dbL = $db->prepare("SELECT l.name FROM form_data_lang f
                                JOIN languages l ON l.id = f.id_lang
                                WHERE f.id_form = ?");
            $dbL->execute([$form_id]);

            $languages = [];
            foreach ($dbL->fetchAll(PDO::FETCH_ASSOC) as $item)
                $languages[] = $item['name'];

            set_val('fio', $nichego['fio']);
            set_val('number', $nichego['number']);
            set_val('email', $nichego['email']);
            set_val('date', date("Y-m-d", $nichego['date']));
            set_val('radio', $nichego['radio']);
            set_val('language', $language);
            set_val('bio', $nichego['bio']);
            set_val('check', "1");
        } catch (PDOException $e) {
            print ('Error : ' . $e->getMessage());
            exit();
        }
    }

    include ('form.php');
}
?>