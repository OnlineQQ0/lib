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

$data = [];

if (isset($_POST['login']) && !empty(trim($_POST['login']))) {
    $searched_login = trim($_POST['login']);
    
    
    $stmt_user = mysqli_prepare($link, "SELECT id FROM users WHERE login = ?");
    mysqli_stmt_bind_param($stmt_user, "s", $searched_login);
    mysqli_stmt_execute($stmt_user);
    mysqli_stmt_bind_result($stmt_user, $searched_user_id);
    mysqli_stmt_fetch($stmt_user);
    mysqli_stmt_close($stmt_user);
    
    if ($searched_user_id) {
        $stmt_current = mysqli_prepare($link, "SELECT id FROM users WHERE login = ?");
        mysqli_stmt_bind_param($stmt_current, "s", $_SESSION['login']);
        mysqli_stmt_execute($stmt_current);
        mysqli_stmt_bind_result($stmt_current, $current_user_id);
        mysqli_stmt_fetch($stmt_current);
        mysqli_stmt_close($stmt_current);
        
        $stmt_access = mysqli_prepare($link, "SELECT id FROM access WHERE owner_id = ? AND guest_id = ?");
        mysqli_stmt_bind_param($stmt_access, "ii", $searched_user_id, $current_user_id);
        mysqli_stmt_execute($stmt_access);
        mysqli_stmt_store_result($stmt_access);
        
        if (mysqli_stmt_num_rows($stmt_access) > 0) {
            $stmt_books = mysqli_prepare($link, "SELECT name FROM books WHERE author_id = ? AND deleted_at IS NULL");
            mysqli_stmt_bind_param($stmt_books, "i", $searched_user_id);
            mysqli_stmt_execute($stmt_books);
            mysqli_stmt_bind_result($stmt_books, $book_name);
            
            $user_books = [];
            while (mysqli_stmt_fetch($stmt_books)) {
                $user_books[] = ['name' => $book_name];
            }
            mysqli_stmt_close($stmt_books);
            
            $data = [
                'user_books' => $user_books,
                'user_id' => $searched_user_id,
                'searched_login' => $searched_login
            ];
        } else {
            $data = [
                'user_books' => [],
                'user_id' => $searched_user_id,
                'searched_login' => $searched_login,
                'error' => 'Нет доступа к книгам этого пользователя'
            ];
        }
        mysqli_stmt_close($stmt_access);
    } else {
        $data = [
            'user_books' => [],
            'user_id' => null,
            'searched_login' => $searched_login,
            'error' => 'Пользователь не найден'
        ];
    }
}



mysqli_close($link);
$current_user = $_SESSION['login'];
include 'search_user.html';
?>