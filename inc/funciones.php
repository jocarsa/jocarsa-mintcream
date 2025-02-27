<?php

	/* ------------------------------------------------------------------
   2. FUNCIONES DE AYUDA
   ------------------------------------------------------------------ */
// Roles: 1 = Superadmin, 2 = Admin Temas, 3 = Admin Hilos, 4 = Usuario, 5 = Visitante
function getUserRole() {
    return isset($_SESSION['role']) ? (int)$_SESSION['role'] : 5; 
}
function getRoleName($role) {
    switch ($role) {
        case 1: return "Superadmin";
        case 2: return "Admin Temas";
        case 3: return "Admin Hilos";
        case 4: return "Usuario";
        default: return "Visitante";
    }
}
// Verifica si el usuario tiene rol menor o igual que $minRole (menor es mayor poder)
function checkRole($minRole) {
    return getUserRole() <= $minRole;
}

	
?>
