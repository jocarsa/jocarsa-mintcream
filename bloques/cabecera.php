<header>
    <nav class="navbar">
        <div class="nav-left">
        	<div id="corporativo">
        		<img src="mintcream.png" id="logo">
            <h1><a href="index.php">jocarsa | mintcream</a></h1>
            </div>
        </div>
        <div class="nav-right">
            <?php if (isset($_SESSION['username'])): ?>
                <div class="user-info">
                    <p>Bienvenido, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                    (<?= getRoleName(getUserRole()) ?>)</p>
                    <?php if (getUserRole() === 1): ?>
                        <a href="?accion=panel" class="button">Panel</a>
                    <?php endif; ?>
                    <a href="?accion=logout" class="button">Cerrar Sesión</a>
                </div>
            <?php else: ?>
                <form action="?accion=login" method="POST" class="login-form">
                    <input type="text" name="username" placeholder="Usuario" required>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <button type="submit">Ingresar</button>
                </form>
                <a href="?accion=register" class="button">Registrarse</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main>
