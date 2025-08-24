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

$stmt_user = mysqli_prepare($link, "SELECT id FROM users WHERE login = ?");
mysqli_stmt_bind_param($stmt_user, "s", $_SESSION['login']);
mysqli_stmt_execute($stmt_user);
mysqli_stmt_bind_result($stmt_user, $user_id);
mysqli_stmt_fetch($stmt_user);
mysqli_stmt_close($stmt_user);

$data = [];

if ($user_id) {
    $stmt_mybooks = mysqli_prepare($link, "SELECT name, text FROM books WHERE id IN (SELECT book_id FROM my_book WHERE user_id = ?) AND deleted_at IS NULL");
    mysqli_stmt_bind_param($stmt_mybooks, "i", $user_id);
    mysqli_stmt_execute($stmt_mybooks);
    mysqli_stmt_bind_result($stmt_mybooks, $name, $text);
    
    while (mysqli_stmt_fetch($stmt_mybooks)) {
        $data[] = [
            'name' => $name,
            'text' => $text
        ];
    }
    mysqli_stmt_close($stmt_mybooks);
}

if(isset($_POST['delete_book'])){
    $name_book = $_POST['delete_book'];
    
    $stmt_book = mysqli_prepare($link, "SELECT id FROM books WHERE name = ? AND deleted_at IS NULL");
    mysqli_stmt_bind_param($stmt_book, "s", $name_book);
    mysqli_stmt_execute($stmt_book);
    mysqli_stmt_bind_result($stmt_book, $book_id);
    mysqli_stmt_fetch($stmt_book);
    mysqli_stmt_close($stmt_book);
    
    if ($book_id) {
        $stmt_delete = mysqli_prepare($link, "DELETE FROM my_book WHERE book_id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt_delete, "ii", $book_id, $user_id);
        mysqli_stmt_execute($stmt_delete);
        echo '<script language="javascript">';
        echo 'alert("вы успешно удалили книгу из своей библиотеки")';
        echo '</script>';
    }
}

mysqli_close($link);
$current_user = $_SESSION['login'];
include 'my_book.html';
?>