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

$current_user = $_SESSION['login'];

$stmt_current = mysqli_prepare($link, "SELECT id FROM users WHERE login = ?");
mysqli_stmt_bind_param($stmt_current, "s", $current_user);
mysqli_stmt_execute($stmt_current);
mysqli_stmt_bind_result($stmt_current, $current_user_id);
mysqli_stmt_fetch($stmt_current);
mysqli_stmt_close($stmt_current);

if (isset($_POST['add_book_name']) && isset($_POST['add_book_text'])) {
    $add_book_name = trim($_POST['add_book_name']);
    $add_book_text = trim($_POST['add_book_text']);
    
    if (!empty($add_book_name) && !empty($add_book_text)) {
        $stmt = mysqli_prepare($link, "INSERT INTO books (name, text, author_id) VALUES (?,?,?)");
        mysqli_stmt_bind_param($stmt, "ssi", $add_book_name, $add_book_text, $current_user_id);
        mysqli_stmt_execute($stmt);
        echo '<script language="javascript">';
        echo 'alert("вы успешно добавили новую книгу")';
        echo '</script>';
    }
}

if (isset($_POST['my_name_book']) && isset($_POST['my_login_book'])) {
    $my_name_book = trim($_POST['my_name_book']);
    $my_login_book = trim($_POST['my_login_book']);
    
    $stmt_author = mysqli_prepare($link, "SELECT id FROM users WHERE login = ?");
    mysqli_stmt_bind_param($stmt_author, "s", $my_login_book);
    mysqli_stmt_execute($stmt_author);
    mysqli_stmt_bind_result($stmt_author, $author_id);
    mysqli_stmt_fetch($stmt_author);
    mysqli_stmt_close($stmt_author);

    $stmt_book = mysqli_prepare($link, "SELECT id FROM books WHERE name = ? AND author_id = ? AND deleted_at IS NULL");
    mysqli_stmt_bind_param($stmt_book, "si", $my_name_book, $author_id);
    mysqli_stmt_execute($stmt_book);
    mysqli_stmt_bind_result($stmt_book, $book_id);
    mysqli_stmt_fetch($stmt_book);
    mysqli_stmt_close($stmt_book);

    if ($book_id) {
        $stmt = mysqli_prepare($link, "INSERT INTO my_book (user_id, book_id) VALUES (?,?)");
        mysqli_stmt_bind_param($stmt, "ii", $current_user_id, $book_id);
        mysqli_stmt_execute($stmt);
        echo '<script language="javascript">';
        echo 'alert("вы успешно добавили пользовательскую книгу в свою библиотеку")';
        echo '</script>';
    } else {
        echo "Книга не найдена или не принадлежит указанному автору";
    }
}

if (isset($_POST['delete_book'])) {
    $delete_book_name = trim($_POST['delete_book']);
    
    if (!empty($delete_book_name)) {
        $stmt = mysqli_prepare($link, "UPDATE books SET deleted_at = CURRENT_TIMESTAMP WHERE name = ? AND author_id = ?");
        mysqli_stmt_bind_param($stmt, "si", $delete_book_name, $current_user_id);
        mysqli_stmt_execute($stmt);
        echo '<script language="javascript">';
        echo 'alert("вы успешно удалили свою книгу из пользовательской библиотеки")';
        echo '</script>';
    }
}

$query = "SELECT u.login as login_author, b.name, b.text 
          FROM books b 
          JOIN users u ON b.author_id = u.id 
          WHERE b.deleted_at IS NULL";
$result = mysqli_query($link, $query);

$data = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}

mysqli_close($link);

include 'book_list.html';
?>