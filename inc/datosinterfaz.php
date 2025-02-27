<?php
/* ------------------------------------------------------------------
   4. OBTENCIÓN DE DATOS PARA MOSTRAR EN LA INTERFAZ
   ------------------------------------------------------------------ */
// Listar temas
$stmt = $pdo->query("SELECT * FROM temas ORDER BY id DESC");
$temas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hilos según tema seleccionado
$tema_seleccionado = isset($_GET['tema_id']) ? (int)$_GET['tema_id'] : 0;
$hilos = [];
if ($tema_seleccionado > 0) {
    $stmt = $pdo->prepare("SELECT * FROM hilos WHERE tema_id = :tid ORDER BY id DESC");
    $stmt->execute([':tid' => $tema_seleccionado]);
    $hilos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Publicaciones según hilo seleccionado
$hilo_seleccionado = isset($_GET['hilo_id']) ? (int)$_GET['hilo_id'] : 0;
$publicaciones = [];
if ($hilo_seleccionado > 0) {
    $stmt = $pdo->prepare("SELECT p.*, u.username 
                           FROM publicaciones p
                           JOIN users u ON p.user_id = u.id
                           WHERE p.hilo_id = :hid 
                           ORDER BY p.id ASC");
    $stmt->execute([':hid' => $hilo_seleccionado]);
    $publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Si se solicita respuesta, obtener mensaje a responder
$reply_message = null;
if (isset($_GET['reply'])) {
    $reply_id = (int)$_GET['reply'];
    $stmt = $pdo->prepare("SELECT * FROM publicaciones WHERE id = :rid");
    $stmt->execute([':rid' => $reply_id]);
    $reply_message = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Si el panel de control está activo, listar usuarios
$lista_usuarios = [];
if (getUserRole() === 1 && $accion === 'panel') {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
    $lista_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
