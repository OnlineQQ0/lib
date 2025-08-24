<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: sign.php");
    exit;
}

$host = "localhost";
$user = "root";
$pass = "root";
$db = "library_db";

$link = mysqli_connect($host, $user, $pass, $db);
if (!$link) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

$query = "SELECT id, login FROM users ORDER BY id ASC";
$result = mysqli_query($link, $query);
if (!$result) {
    die("Ошибка выполнения запроса: " . mysqli_error($link));
}

if (isset($_POST['add_login'])) {
    $add_access = trim($_POST['add_login']);
    
    if (!empty($add_access)) {
        $stmt_guest = mysqli_prepare($link, "SELECT id FROM users WHERE login = ?");
        mysqli_stmt_bind_param($stmt_guest, "s", $add_access);
        mysqli_stmt_execute($stmt_guest);
        mysqli_stmt_bind_result($stmt_guest, $guest_id);
        mysqli_stmt_fetch($stmt_guest);
        mysqli_stmt_close($stmt_guest);
        
        if ($guest_id) {
            $stmt = mysqli_prepare($link, "INSERT INTO access (owner_id, guest_id) VALUES (?,?)");
            mysqli_stmt_bind_param($stmt, "ii", $_SESSION['user_id'], $guest_id);
            mysqli_stmt_execute($stmt);
            echo '<script language="javascript">';
            echo 'alert("вы успешно разрешили пользователю доступ к вашей библиотеке")';
            echo '</script>';
        }
    }
}
if (isset($_POST['del_login'])) {
    $del_access = trim($_POST['del_login']);
    
    if (!empty($del_access)) {
        $stmt_guest = mysqli_prepare($link, "SELECT id FROM users WHERE login = ?");
        mysqli_stmt_bind_param($stmt_guest, "s", $del_access);
        mysqli_stmt_execute($stmt_guest);
        mysqli_stmt_bind_result($stmt_guest, $guest_id);
        mysqli_stmt_fetch($stmt_guest);
        mysqli_stmt_close($stmt_guest);
        
        if ($guest_id) {
            $stmt = mysqli_prepare($link, "DELETE FROM access WHERE owner_id = ? AND guest_id = ?");
            mysqli_stmt_bind_param($stmt, "ii", $_SESSION['user_id'], $guest_id);
            mysqli_stmt_execute($stmt);
            echo '<script language="javascript">';
            echo 'alert("вы успешно запретили пользователю доступ к вашей библиотеке")';
            echo '</script>';
        }
    }
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

mysqli_close($link);
$current_user = $_SESSION['login'];
include 'participants.html';
?>
