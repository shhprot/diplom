<?php
header('Content-Type: application/json; charset=utf-8');

// Подключаем конфигурацию БД
require_once __DIR__ . '/config/db.php';

try {
    // Проверяем метод запроса
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Допустимы только POST-запросы', 405);
    }

    // Проверяем обязательные поля
    $requiredFields = ['name', 'phone', 'group'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Не заполнено обязательное поле: {$field}", 400);
        }
    }

    // Получаем и очищаем данные
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $group = trim($_POST['group']);

    // Валидация данных
    if (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]{2,50}$/u', $name)) {
        throw new Exception('Некорректное имя (допустимы только буквы и дефисы)', 400);
    }

    if (!preg_match('/^[\d\s\-\+\(\)]{7,20}$/', $phone)) {
        throw new Exception('Некорректный номер телефона', 400);
    }

    // Подготовленный запрос для защиты от SQL-инъекций
    $stmt = $conn->prepare("INSERT INTO registrations (name, phone, group_name, reg_date) VALUES (?, ?, ?, NOW())");
    if (!$stmt) {
        throw new Exception('Ошибка подготовки запроса: ' . $conn->error, 500);
    }

    $stmt->bind_param("sss", $name, $phone, $group);
    
    if (!$stmt->execute()) {
        throw new Exception('Ошибка выполнения запроса: ' . $stmt->error, 500);
    }

    // Успешный ответ
    echo json_encode([
        'success' => true,
        'message' => 'Вы успешно записаны на тренировку!'
    ]);

} catch (Exception $e) {
    // Обработка ошибок
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    // Закрываем соединение
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}