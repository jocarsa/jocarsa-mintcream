<?php
	
	/* ------------------------------------------------------------------
   1. CONEXIÓN A LA BASE DE DATOS SQLITE Y CREACIÓN DE TABLAS
   ------------------------------------------------------------------ */
$db_file = '../databases/mintcream.db';
try {
    $pdo = new PDO("sqlite:$db_file");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Tabla de usuarios con campos adicionales
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role INTEGER NOT NULL,
        name TEXT,
        email TEXT
    )");

    // Tabla de temas
    $pdo->exec("CREATE TABLE IF NOT EXISTS temas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titulo TEXT NOT NULL
    )");

    // Tabla de hilos
    $pdo->exec("CREATE TABLE IF NOT EXISTS hilos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tema_id INTEGER NOT NULL,
        titulo TEXT NOT NULL,
        FOREIGN KEY (tema_id) REFERENCES temas(id) ON DELETE CASCADE
    )");

    // Tabla de publicaciones (agregamos parent_id para respuestas)
    $pdo->exec("CREATE TABLE IF NOT EXISTS publicaciones (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        hilo_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        parent_id INTEGER DEFAULT 0,
        contenido TEXT NOT NULL,
        fecha DATETIME NOT NULL,
        FOREIGN KEY (hilo_id) REFERENCES hilos(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Crear Superadmin inicial (si no existe)
    // Nombre: Jose Vicente Carratala, user: jocarsa, pass: jocarsa, role=1
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'jocarsa'");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, name, email) 
                               VALUES (:u, :p, :r, :n, :e)");
        $stmt->execute([
            ':u' => 'jocarsa',
            ':p' => 'jocarsa', // En producción usar password_hash()
            ':r' => 1,         // Superadmin
            ':n' => 'Jose Vicente Carratala',
            ':e' => 'info@josevicentecarratala'
        ]);
    }
} catch (PDOException $e) {
    echo "Error con la base de datos: " . $e->getMessage();
    exit;
}
?>
