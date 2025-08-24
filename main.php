<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: sign.php");
    exit;
}

$from = $_SESSION['from'] ?? 'sign';
unset($_SESSION['from']);
?>

<script>
const userData = {
    login: "<?= htmlspecialchars($_SESSION['login']) ?>",
    from: "<?= $from ?>"
};
</script>


<?php include 'main.html'; ?>
