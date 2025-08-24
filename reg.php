<?php
include 'reg.html';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Доступ запрещён. Форма не отправлена.");
}

if (!isset($_POST['login']) || !isset($_POST['password']) || !isset($_POST['c_pass'])) {
    die("Недостаточно данных для регистрации.");
}

$login = trim($_POST['login']);
$password = trim($_POST['password']);
$c_pass = trim($_POST['c_pass']);

if (empty($login) || empty($password) || empty($c_pass)) {
    die("Все поля обязательны для заполнения.");
}

if ($password !== $c_pass) {
    die("Пароли не совпадают.");
}

$host = "localhost";
$user = "root";
$pass = "root"; 
$db = "library_db";

$link = mysqli_connect($host, $user, $pass, $db);

if (!$link) {
    die("Ошибка подключения к БД: " . mysqli_connect_error());
}

$check = mysqli_prepare($link, "SELECT id FROM users WHERE login = ?");
if (!$check) {
    die("Ошибка подготовки запроса: " . mysqli_error($link));
}

mysqli_stmt_bind_param($check, "s", $login);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);

if (mysqli_stmt_num_rows($check) > 0) {
    die("Пользователь с таким логином уже существует.");
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($link, "INSERT INTO users (login, password) VALUES (?, ?)");
if (!$stmt) {
    die("Ошибка подготовки запроса: " . mysqli_error($link));
}

mysqli_stmt_bind_param($stmt, "ss", $login, $hashed_password);

if (mysqli_stmt_execute($stmt)) {
    $user_id = mysqli_insert_id($link);

    $_SESSION["user_id"] = $user_id;
    $_SESSION["login"] = $login;

    header("Location: main.php");
    exit;
} else {
    echo "<p style='color: red;'>Ошибка при регистрации: " . mysqli_error($link) . "</p>";
}

mysqli_stmt_close($stmt);
mysqli_stmt_close($check);
mysqli_close($link);

?>
