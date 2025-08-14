<?php
// init_db.php — запускать один раз для создания таблицы

try {
    $db = new PDO('sqlite:db.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("
        CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            status TEXT DEFAULT 'pending'
        )
    ");

    echo "База и таблица созданы!\n";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
