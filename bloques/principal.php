<?php if ($accion === 'register'): ?>
    <!-- FORMULARIO DE REGISTRO -->
    <section class="registration">
        <h2>Registrarse</h2>
        <form action="?accion=register" method="POST" class="form-vertical">
            <input type="text" name="username" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="text" name="name" placeholder="Nombre completo">
            <input type="email" name="email" placeholder="Email">
            <button type="submit">Registrarse</button>
        </form>
    </section>
<?php elseif ($accion === 'panel' && getUserRole() === 1): ?>
    <!-- PANEL DE CONTROL PARA SUPERADMIN -->
    <section class="panel-control">
        <h2>Panel de Control (Superadmin)</h2>
        <div class="panel-section">
            <h3>Crear Nuevo Usuario</h3>
            <form method="POST" action="?accion=panel">
                <input type="hidden" name="panel_action" value="create_user">
                <label>Username:
                    <input type="text" name="username" required>
                </label>
                <label>Password:
                    <input type="text" name="password" required>
                </label>
                <label>Role:
                    <select name="role">
                        <option value="1">Superadmin</option>
                        <option value="2">Admin Temas</option>
                        <option value="3">Admin Hilos</option>
                        <option value="4" selected>Usuario</option>
                        <option value="5">Visitante</option>
                    </select>
                </label>
                <label>Name:
                    <input type="text" name="name">
                </label>
                <label>Email:
                    <input type="email" name="email">
                </label>
                <button type="submit">Crear Usuario</button>
            </form>
        </div>
        <div class="panel-section">
            <h3>Usuarios Existentes</h3>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($lista_usuarios as $usr): ?>
                    <tr>
                        <td><?= $usr['id'] ?></td>
                        <td><?= htmlspecialchars($usr['username']) ?></td>
                        <td><?= htmlspecialchars($usr['name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($usr['email'] ?? '') ?></td>
                        <td><?= getRoleName($usr['role']) ?></td>
                        <td>
                            <!-- Actualizar Rol -->
                            <form method="POST" action="?accion=panel" class="inline-form">
                                <input type="hidden" name="panel_action" value="update_role">
                                <input type="hidden" name="user_id" value="<?= $usr['id'] ?>">
                                <select name="new_role">
                                    <option value="1" <?= $usr['role'] == 1 ? 'selected' : '' ?>>Superadmin</option>
                                    <option value="2" <?= $usr['role'] == 2 ? 'selected' : '' ?>>Admin Temas</option>
                                    <option value="3" <?= $usr['role'] == 3 ? 'selected' : '' ?>>Admin Hilos</option>
                                    <option value="4" <?= $usr['role'] == 4 ? 'selected' : '' ?>>Usuario</option>
                                    <option value="5" <?= $usr['role'] == 5 ? 'selected' : '' ?>>Visitante</option>
                                </select>
                                <button type="submit">Actualizar</button>
                            </form>
                            <!-- Eliminar Usuario -->
                            <form method="POST" action="?accion=panel" class="inline-form">
                                <input type="hidden" name="panel_action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?= $usr['id'] ?>">
                                <button type="submit" onclick="return confirm('¿Eliminar este usuario?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php else: ?>
    <!-- INTERFAZ PRINCIPAL CON LAYOUT DE TRES COLUMNAS -->
    <div class="three-columns">
        <!-- Columna Izquierda: Temas -->
        <div class="left-column">
            <section class="temas">
                <h2>Temas</h2>
                <ul>
                    <?php foreach($temas as $tema): ?>
                        <li>
                            <a href="?tema_id=<?= $tema['id'] ?>">
                                <?= htmlspecialchars($tema['titulo']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (checkRole(2)): ?>
                <form action="?accion=crear_tema" method="POST" class="form-inline">
                    <input type="text" name="titulo" placeholder="Nuevo Tema" required>
                    <button type="submit">Crear</button>
                </form>
                <?php endif; ?>
            </section>
        </div>
        <!-- Columna Central: Hilos -->
        <div class="center-column">
            <?php if ($tema_seleccionado > 0): ?>
            <section class="hilos">
                <h2>Hilos</h2>
                <ul>
                    <?php foreach($hilos as $hilo): ?>
                        <li>
                            <a href="?tema_id=<?= $tema_seleccionado ?>&hilo_id=<?= $hilo['id'] ?>">
                                <?= htmlspecialchars($hilo['titulo']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (checkRole(3)): ?>
                <form action="?accion=crear_hilo" method="POST" class="form-inline">
                    <input type="hidden" name="tema_id" value="<?= $tema_seleccionado ?>">
                    <input type="text" name="titulo" placeholder="Nuevo Hilo" required>
                    <button type="submit">Crear</button>
                </form>
                <?php endif; ?>
            </section>
            <?php endif; ?>
        </div>
        <!-- Columna Derecha: Publicaciones -->
        <div class="right-column">
            <?php if ($hilo_seleccionado > 0): ?>
            <section class="publicaciones">
                <h2>Mensajes</h2>
                <ul>
                    <?php foreach($publicaciones as $pub): ?>
                        <li>
                            <div class="pub-header">
                                <span class="pub-autor"><?= htmlspecialchars($pub['username']) ?></span>
                                <span class="pub-fecha"><?= $pub['fecha'] ?></span>
                                <a href="?tema_id=<?= $tema_seleccionado ?>&hilo_id=<?= $hilo_seleccionado ?>&reply=<?= $pub['id'] ?>" class="reply-link">Responder</a>
                            </div>
                            <div class="pub-contenido"><?= nl2br(htmlspecialchars($pub['contenido'])) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (checkRole(4)): ?>
                <form action="?accion=crear_publicacion" method="POST" class="form-vertical">
                    <input type="hidden" name="hilo_id" value="<?= $hilo_seleccionado ?>">
                    <?php if ($reply_message): ?>
                        <p class="reply-info">
                            Respondiendo a <?= htmlspecialchars($reply_message['username']) ?>:
                            <em><?= substr($reply_message['contenido'],0,50) ?>...</em>
                        </p>
                        <input type="hidden" name="parent_id" value="<?= $reply_message['id'] ?>">
                    <?php else: ?>
                        <input type="hidden" name="parent_id" value="0">
                    <?php endif; ?>
                    <textarea name="contenido" placeholder="Escribe tu mensaje aquí" required></textarea>
                    <button type="submit">Publicar</button>
                </form>
                <?php endif; ?>
            </section>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
