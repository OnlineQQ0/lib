<?php
include 'sign.html';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Доступ запрещён.");
}
$login = trim($_POST['login']);
$password = trim($_POST['password']);

if (empty($login) || empty($password)) {
    die("Все поля обязательны для заполнения.");
}

$host = "localhost";
$user = "root";
$pass = "root"; 
$db = "library_db";

$link = mysqli_connect($host, $user, $pass, $db);

if (!$link) {
    die("Ошибка подключения к БД: " . mysqli_connect_error());
}

$stmt = mysqli_prepare($link, "SELECT id, login, password FROM users WHERE login = ?");
mysqli_stmt_bind_param($stmt, "s", $login);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $hash = $row["password"];
    if(!password_verify($password, $hash)){
        echo "<h2>Ошибка</h2>";
        echo "<p>Неверный пароль.</p>";
    }else{
        $_SESSION["user_id"] = $row["id"];
        $_SESSION["login"] = $row["login"];
        header("Location: main.php");
        exit;}        
    } else {
        echo "<h2>Ошибка</h2>";
        echo "<p>Пользователь с таким логином не найден.</p>";
    }

mysqli_stmt_close($stmt);
mysqli_close($link);

?>
