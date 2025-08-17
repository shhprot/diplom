<?php
// Убедитесь, что нет пробелов/переносов до этого тега!

// Отключаем вывод любых данных кроме JSON
ob_start();

// Устанавливаем заголовки в самом начале
header('Content-Type: application/json; charset=utf-8');

// Настройки ошибок (для разработки)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Подключаем конфиг БД
require_once __DIR__.'/config/db.php';

// Функция для отправки JSON
function sendJson($success, $message = '', $error = '') {
    ob_end_clean(); // Очищаем буфер вывода
    exit(json_encode([
        'success' => $success,
        'message' => $message,
        'error' => $error
    ], JSON_UNESCAPED_UNICODE));
}

try {
    // Проверка метода запроса
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJson(false, '', 'Only POST allowed');
    }

    // Проверка обязательных полей
    $required = ['name', 'phone', 'location', 'group'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            sendJson(false, '', "Missing field: $field");
        }
    }

    // Очистка данных
    $name = trim($_POST['name']);
    $phone = preg_replace('/[^0-9+]/', '', $_POST['phone']);
    $location = $_POST['location'];
    $group = $_POST['group'];

    // Подключение к БД
    $db = getDBConnection();

    // Подготовленный запрос
    $stmt = $db->prepare("INSERT INTO registrations 
                         (name, phone, location, group_name, reg_date) 
                         VALUES (?, ?, ?, ?, NOW())");
    if (!$stmt) {
        throw new Exception("Prepare failed: ".$db->error);
    }

    $stmt->bind_param("ssss", $name, $phone, $location, $group);
    
    if ($stmt->execute()) {
        sendJson(true, 'Registration successful');
    } else {
        throw new Exception("Execute failed: ".$stmt->error);
    }

} catch (Exception $e) {
    error_log("Error: ".$e->getMessage());
    sendJson(false, '', 'Server error');
}

// Данные из формы
$name = htmlspecialchars($_POST['name']);
$phone = htmlspecialchars($_POST['phone']);
$group = htmlspecialchars($_POST['group']);
$location = htmlspecialchars($_POST['location']);

// Email получателя
$to = 'myagkiy06@mail.ru'; // Ваша почта

// Формируем письмо
$subject = "=?utf-8?B?" . base64_encode("Новая запись: $name") . "?=";
$message = "
<html>
<head>
    <title>Новая запись</title>
</head>
<body>
    <h2>Новая запись в White Wolf</h2>
    <p><strong>Имя:</strong> $name</p>
    <p><strong>Телефон:</strong> $phone</p>
    <p><strong>Группа:</strong> $group</p>
    <p><strong>Филиал:</strong> $location</p>
    <p><em>" . date('d.m.Y H:i') . "</em></p>
</body>
</html>
";

// Заголовки
$headers = "From: example@gmail.com\r\n"; // Должен совпадать с SMTP-логином!
$headers .= "Content-Type: text/html; charset=utf-8\r\n";

// Отправка
if (mail($to, $subject, $message, $headers)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => error_get_last()['message']]);
}