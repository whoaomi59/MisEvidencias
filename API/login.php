<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, password_hash FROM usuarios WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        // Verifica contraseña, asumiendo que usas password_hash
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            header('Location: app.php'); // Página principal o dashboard
            exit;
        }
    }

    $error = "Usuario o contraseña incorrectos";
}
?>

<form method="POST">
    Usuario: <input name="username" required />
    Contraseña: <input name="password" type="password" required />
    <button type="submit">Entrar</button>
</form>

<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
