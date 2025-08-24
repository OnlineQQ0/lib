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


function getBookData($query) { 
    $apiUrl = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($query); 
    $response = file_get_contents($apiUrl); 
    return json_decode($response, true);
}
if (isset($_POST['name']) && !empty(trim($_POST['name']))) {
    $name_book = trim($_POST['name']);
    $search_book = getBookData($name_book);
    
    if (isset($search_book['items'])) {
        
        
        
        foreach ($search_book['items'] as &$item) {
            $item['volumeInfo']['links'] = [];
            
            if (isset($item['volumeInfo']['canonicalVolumeLink'])) {
                $item['volumeInfo']['links'][] = [
                    'text' => 'Перейти к книге',
                    'url' => $item['volumeInfo']['canonicalVolumeLink']
                ];
            }
            
            if (isset($item['volumeInfo']['description'])) {
                $item['volumeInfo']['links'][] = [
                    'text' => 'Просмотр',
                    'url' => $item['volumeInfo']['previewLink']
                ];
            }
        }
    }
}

if (isset($_POST['name']) && !empty(trim($_POST['name']))) {
    $name_book = trim($_POST['name']);
    $search_book = getBookData($name_book);
}


if (isset($_POST['name']) && !empty(trim($_POST['name']))) {
    $name = $_POST['name'];
    if (isset($_POST['description']) && !empty(trim($_POST['description']))) {
        $description = $_POST['description'];
        
        $user_stmt = mysqli_prepare($link, "SELECT id FROM users WHERE login = ?");
        mysqli_stmt_bind_param($user_stmt, "s", $current_user);
        mysqli_stmt_execute($user_stmt);
        mysqli_stmt_bind_result($user_stmt, $author_id);
        mysqli_stmt_fetch($user_stmt);
        mysqli_stmt_close($user_stmt);
        
        $stmt = mysqli_prepare($link, "INSERT INTO books (name, author_id, text) VALUES(?,?,?)");
        mysqli_stmt_bind_param($stmt, "sis", $name, $author_id, $description);
        mysqli_stmt_execute($stmt);
        echo '<script language="javascript">';
        echo 'alert("вы успешно добавили книгу в свою библиотеку")';
        echo '</script>';
    }
}



mysqli_close($link);


$data = [
    'items' => isset($search_book['items']) ? $search_book['items'] : []
];
include 'search_book.html';
?>