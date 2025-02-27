<?php
/* ------------------------------------------------------------------
   3. PROCESAMIENTO DE FORMULARIOS
   ------------------------------------------------------------------ */
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

switch ($accion) {

    /* 3.1 LOGIN / LOGOUT */
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u LIMIT 1");
            $stmt->execute([':u' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && $user['password'] === $password) { // Usar password_verify en producción
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
            } else {
                echo "<p style='color:red;'>Usuario o contraseña incorrectos</p>";
            }
        }
        break;
    
    case 'logout':
        session_destroy();
        header("Location: index.php");
        exit;
        break;

    /* 3.2 REGISTRO DE USUARIOS (autoregistro) */
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            if (empty($username) || empty($password)) {
                echo "<p style='color:red;'>Usuario y contraseña son requeridos.</p>";
            } else {
                // Verificar si el usuario ya existe
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :u");
                $stmt->execute([':u' => $username]);
                if ($stmt->fetchColumn() > 0) {
                    echo "<p style='color:red;'>El usuario ya existe.</p>";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, name, email)
                                           VALUES (:u, :p, 4, :n, :e)");
                    $stmt->execute([
                        ':u' => $username,
                        ':p' => $password, // Usar password_hash() en producción
                        ':n' => $name,
                        ':e' => $email
                    ]);
                    // Iniciar sesión automáticamente
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u LIMIT 1");
                    $stmt->execute([':u' => $username]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($user) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                    }
                    header("Location: index.php");
                    exit;
                }
            }
        }
        break;

    /* 3.3 CREAR TEMA (solo roles <= 2) */
    case 'crear_tema':
        if (checkRole(2) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $titulo = trim($_POST['titulo'] ?? '');
            if (!empty($titulo)) {
                $stmt = $pdo->prepare("INSERT INTO temas (titulo) VALUES (:t)");
                $stmt->execute([':t' => $titulo]);
            }
        }
        break;

    /* 3.4 CREAR HILO (solo roles <= 3) */
    case 'crear_hilo':
        if (checkRole(3) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $tema_id = (int)($_POST['tema_id'] ?? 0);
            $titulo  = trim($_POST['titulo'] ?? '');
            if ($tema_id > 0 && !empty($titulo)) {
                $stmt = $pdo->prepare("INSERT INTO hilos (tema_id, titulo) VALUES (:tid, :t)");
                $stmt->execute([':tid' => $tema_id, ':t' => $titulo]);
            }
        }
        break;

    /* 3.5 CREAR PUBLICACIÓN (solo roles <= 4) */
    case 'crear_publicacion':
        if (checkRole(4) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $hilo_id = (int)($_POST['hilo_id'] ?? 0);
            $contenido = trim($_POST['contenido'] ?? '');
            $parent_id = (int)($_POST['parent_id'] ?? 0);
            $user_id = $_SESSION['user_id'] ?? 0;
            if ($hilo_id > 0 && !empty($contenido) && $user_id > 0) {
                $stmt = $pdo->prepare("INSERT INTO publicaciones (hilo_id, user_id, parent_id, contenido, fecha) 
                                       VALUES (:hid, :uid, :pid, :c, :f)");
                $stmt->execute([
                    ':hid' => $hilo_id,
                    ':uid' => $user_id,
                    ':pid' => $parent_id,
                    ':c'   => $contenido,
                    ':f'   => date('Y-m-d H:i:s')
                ]);
                // Redirigir al mismo hilo para permanecer en él
                $stmt2 = $pdo->prepare("SELECT tema_id FROM hilos WHERE id = :hid");
                $stmt2->execute([':hid' => $hilo_id]);
                $tema_id = $stmt2->fetchColumn();
                header("Location: index.php?tema_id=$tema_id&hilo_id=$hilo_id");
                exit;
            }
        }
        break;

    /* 3.6 PANEL DE CONTROL (SUPERADMIN) */
    case 'panel':
        if (getUserRole() === 1) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Crear usuario (usa dropdown para rol)
                if (isset($_POST['panel_action']) && $_POST['panel_action'] === 'create_user') {
                    $uname  = trim($_POST['username'] ?? '');
                    $pass   = trim($_POST['password'] ?? '');
                    $urole  = (int)($_POST['role'] ?? 5);
                    $uname2 = trim($_POST['name'] ?? '');
                    $uemail = trim($_POST['email'] ?? '');
                    if (!empty($uname) && !empty($pass)) {
                        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, name, email)
                                               VALUES (:u, :p, :r, :n, :e)");
                        $stmt->execute([
                            ':u' => $uname,
                            ':p' => $pass, // Usar password_hash() en producción
                            ':r' => $urole,
                            ':n' => $uname2,
                            ':e' => $uemail
                        ]);
                    }
                }
                // Eliminar usuario
                if (isset($_POST['panel_action']) && $_POST['panel_action'] === 'delete_user') {
                    $del_id = (int)($_POST['user_id'] ?? 0);
                    if ($del_id > 0 && $del_id !== (int)$_SESSION['user_id']) {
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                        $stmt->execute([':id' => $del_id]);
                    }
                }
                // Actualizar rol
                if (isset($_POST['panel_action']) && $_POST['panel_action'] === 'update_role') {
                    $upd_id = (int)($_POST['user_id'] ?? 0);
                    $new_role = (int)($_POST['new_role'] ?? 5);
                    if ($upd_id > 0) {
                        $stmt = $pdo->prepare("UPDATE users SET role = :r WHERE id = :id");
                        $stmt->execute([':r' => $new_role, ':id' => $upd_id]);
                    }
                }
            }
        }
        break;
}

?>
