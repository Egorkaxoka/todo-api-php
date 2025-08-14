<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$db = new PDO('sqlite:db.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Получение ID из URL
$uri = explode("/", trim($_SERVER["REQUEST_URI"], "/"));
$id = isset($uri[1]) ? (int)$uri[1] : null;

// Обработка OPTIONS (CORS preflight)
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit(0);
}

// CRUD
switch ($_SERVER["REQUEST_METHOD"]) {
    case "GET":
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM tasks WHERE id = ?");
            $stmt->execute([$id]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($task ?: ["error" => "Task not found"]);
        } else {
            $tasks = $db->query("SELECT * FROM tasks")->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($tasks);
        }
        break;

    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data["title"])) {
            http_response_code(400);
            echo json_encode(["error" => "Title is required"]);
            break;
        }
        $stmt = $db->prepare("INSERT INTO tasks (title, description, status) VALUES (?, ?, ?)");
        $stmt->execute([$data["title"], $data["description"] ?? "", $data["status"] ?? "pending"]);
        echo json_encode(["id" => $db->lastInsertId()]);
        break;

    case "PUT":
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Task ID is required"]);
            break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $db->prepare("UPDATE tasks SET title = ?, description = ?, status = ? WHERE id = ?");
        $stmt->execute([
            $data["title"] ?? "",
            $data["description"] ?? "",
            $data["status"] ?? "pending",
            $id
        ]);
        echo json_encode(["updated" => $stmt->rowCount()]);
        break;

    case "DELETE":
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Task ID is required"]);
            break;
        }
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["deleted" => $stmt->rowCount()]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
}
