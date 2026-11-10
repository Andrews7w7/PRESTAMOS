<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$host = '127.0.0.1';
$db = 'proveeduria';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (PDOException $exception) {
    http_response_code(500);
    exit('<div style="font-family:Arial;padding:2rem"><h1>No se pudo conectar a MySQL</h1><p>Crea la base de datos importando <strong>database.sql</strong> en phpMyAdmin y verifica que MySQL esté iniciado.</p><small>' . htmlspecialchars($exception->getMessage()) . '</small></div>');
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function spanishDate(): string
{
    $days = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    $months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $timestamp = time();
    return ucfirst($days[(int) date('w', $timestamp)]) . ', ' . date('d', $timestamp) . ' de ' . $months[(int) date('n', $timestamp) - 1] . ' de ' . date('Y', $timestamp);
}

function redirectWithMessage(string $message, string $type = 'success'): never
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    header('Location: index.php');
    exit;
}

if (($_GET['action'] ?? '') === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

$loginError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $username = trim($_POST['username'] ?? '');
    $loginPassword = $_POST['password'] ?? '';
    $loginStatement = $pdo->prepare('SELECT * FROM users WHERE username = ? AND active = 1 LIMIT 1');
    $loginStatement->execute([$username]);
    $loginUser = $loginStatement->fetch();
    if ($loginUser && password_verify($loginPassword, $loginUser['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $loginUser['id'];
        $_SESSION['user_name'] = $loginUser['full_name'];
        header('Location: index.php');
        exit;
    }
    $loginError = 'Usuario o contraseña incorrectos.';
}

if (empty($_SESSION['user_id'])) {
    $errorHtml = $loginError ? '<div class="login-error"><i class="bi bi-exclamation-circle-fill"></i> ' . e($loginError) . '</div>' : '';
    $loginPage = <<<'HTML'
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Acceso | Titanio</title><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><style>*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:28px;background:#242323;font:15px "DM Sans",sans-serif;color:#302a27}.login-layout{display:grid;grid-template-columns:1fr 1fr;width:min(930px,100%);min-height:570px;background:#fffdfb;border-radius:22px;overflow:hidden;box-shadow:0 30px 90px #0009}.login-brand{position:relative;display:flex;flex-direction:column;justify-content:center;padding:52px;background:linear-gradient(145deg,#1b1b1b,#37312e);color:#fff}.login-brand:after{content:"";position:absolute;width:260px;height:260px;border-radius:50%;right:-90px;top:-80px;background:#f97316;opacity:.9}.login-brand:before{content:"";position:absolute;width:150px;height:150px;border:1px solid #ffffff33;border-radius:50%;left:-55px;bottom:-55px}.login-logo{position:relative;z-index:1;width:min(245px,90%);height:auto;border-radius:9px;box-shadow:0 15px 35px #0006;margin-bottom:35px}.brand-kicker{position:relative;z-index:1;color:#ff9a4d;font-weight:700;letter-spacing:2px;text-transform:uppercase;font-size:12px}.login-brand h2{position:relative;z-index:1;font:700 32px "Space Grotesk",sans-serif;line-height:1.1;margin:12px 0}.brand-copy{position:relative;z-index:1;color:#d8cdca;line-height:1.6;max-width:320px}.brand-line{position:relative;z-index:1;width:55px;height:4px;border-radius:4px;background:#f97316;margin-top:22px}.login-card{padding:65px 55px;display:flex;flex-direction:column;justify-content:center}.login-card h1{font:700 34px "Space Grotesk",sans-serif;margin:0 0 8px;color:#282321}.login-subtitle{color:#83766e;margin:0 0 28px}.login-overline{color:#e56a15;font-size:11px;font-weight:700;letter-spacing:1.7px;text-transform:uppercase;margin-bottom:14px}.login-field{display:block;font-weight:700;color:#665950;margin:16px 0 7px}.login-field input{width:100%;padding:14px;border:1px solid #dfd4cc;border-radius:9px;font:15px "DM Sans";background:#fff}.login-field input:focus{outline:0;border-color:#f97316;box-shadow:0 0 0 4px #f9731626}.login-button{width:100%;border:0;border-radius:9px;padding:15px;margin-top:25px;background:#f97316;color:#fff;font:700 15px "DM Sans";cursor:pointer;box-shadow:0 9px 20px #f9731640}.login-button:hover{background:#d85c09;transform:translateY(-1px)}.login-footer{color:#9a8b82;text-align:center;font-size:12px;margin-top:25px}.login-error{padding:12px 14px;border-radius:9px;background:#fff0eb;color:#ae412e;font-weight:600;margin-bottom:15px}.login-error i{margin-right:5px}@media(max-width:700px){body{padding:14px}.login-layout{display:block;min-height:0}.login-brand{padding:28px 26px;min-height:245px}.login-logo{width:150px;margin-bottom:20px}.login-brand h2{font-size:25px;margin:8px 0}.brand-copy,.brand-line{display:none}.login-brand:after{width:150px;height:150px;right:-50px;top:-50px}.login-card{padding:34px 26px 30px}}
</style></head><body><main class="login-layout"><section class="login-brand"><img class="login-logo" src="TITANIO-VERTICAL-FONDO-NEGRO.png" alt="Titanio"><div class="brand-kicker">Control interno</div><h2>Proveeduría<br>con orden y control.</h2><p class="brand-copy">Registra cada salida, conserva el historial y mantén la operación de tu empresa organizada.</p><span class="brand-line"></span></section><section class="login-card"><div class="login-overline"><i class="bi bi-shield-lock-fill"></i> Acceso seguro</div><h1>Bienvenido</h1><p class="login-subtitle">Ingresa para continuar al panel de control.</p>{{ERROR}}<form method="post"><input type="hidden" name="action" value="login"><label class="login-field" for="username">Usuario</label><input class="login-field" id="username" name="username" type="text" autocomplete="username" required><label class="login-field" for="password">Contraseña</label><input class="login-field" id="password" name="password" type="password" autocomplete="current-password" required><button class="login-button" type="submit"><i class="bi bi-box-arrow-in-right"></i> Iniciar sesión</button></form><div class="login-footer"><i class="bi bi-lock-fill"></i> Acceso protegido para personal autorizado</div></section></main></body></html>
HTML;
    $loginPage = str_replace('TITANIO-VERTICAL-FONDO-NEGRO.png', 'Gemini_Generated_Image_h43kklh43kklh43k.jpg', $loginPage);
    $loginPage = str_replace('</section></main>', '<div class="login-quote" id="login-quote"></div></section></main><script>const loginQuotes = ["Cada pequeño avance cuenta.", "La constancia convierte los planes en resultados.", "El orden de hoy facilita el trabajo de mañana.", "Quien persevera, alcanza sus metas.", "La disciplina es el puente entre las metas y los logros.", "Un buen equipo transforma los retos en oportunidades."]; const quoteElement = document.getElementById("login-quote"); let quoteIndex = Math.floor(Date.now() / 60000) % loginQuotes.length; const showLoginQuote = () => { quoteElement.textContent = "“" + loginQuotes[quoteIndex] + "”"; quoteIndex = (quoteIndex + 1) % loginQuotes.length; }; showLoginQuote(); setInterval(showLoginQuote, 60000);</script>', $loginPage);
    $loginPage = str_replace('Registra cada salida, conserva el historial y mantén la operación de tu empresa organizada.', 'Registra cada salida, conserva el historial y mantén la operación del departamento.', $loginPage);
    $loginPage = str_replace('{{ERROR}}', $errorHtml, $loginPage);
    $styleCount = 0;
    $loginPage = str_replace('</style>', 'input.login-field{display:block;width:100%;height:52px;margin:0 0 20px;padding:0 16px;border:1px solid #dfd4cc;border-radius:10px;background:#fff;color:#302a27;font:500 15px "DM Sans",sans-serif;box-shadow:0 5px 14px rgba(48,42,39,.05);transition:border-color .2s ease,box-shadow .2s ease,transform .2s ease}input.login-field:hover{border-color:#cbbab0}input.login-field:focus{outline:0;border-color:#f97316;box-shadow:0 0 0 4px #f9731626,0 8px 18px rgba(48,42,39,.08);transform:translateY(-1px)}input.login-field::placeholder{color:#b1a39a}.login-card label.login-field{display:block;margin:18px 0 7px;color:#665950;font-size:13px;font-weight:700;letter-spacing:.2px}.login-card label.login-field:first-of-type{margin-top:0}.login-brand:after{display:none!important}.login-brand:before{display:none!important}.login-quote{min-height:22px;margin-top:18px;text-align:center;color:#a4775b;font:italic 13px "DM Sans",sans-serif;line-height:1.45}</style>', $loginPage, $styleCount);
    $loginPage = str_replace('</section></main>', '<div style="margin-top:18px;text-align:center;color:#a18f84;font-size:11px;letter-spacing:.5px">Powered by <strong style="color:#d85c09;font-weight:800">andrews sierra</strong></div></section></main>', $loginPage);
    exit($loginPage);
}

if (empty($_SESSION['user_id'])) {
    $errorHtml = $loginError ? '<div class="login-error">' . e($loginError) . '</div>' : '';
    exit('<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Acceso | Titanio</title><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet"><style>*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(circle at 15% 15%,#f9731640,transparent 25rem),linear-gradient(135deg,#171616,#302b29);font:15px "DM Sans",sans-serif;color:#302a27}.login-card{width:min(430px,100%);background:#fffdfb;border-radius:18px;padding:42px;box-shadow:0 25px 80px #0008}.login-logo{display:block;width:150px;height:auto;margin:0 auto 25px;border-radius:8px}.login-card h1{text-align:center;font:700 29px "Space Grotesk",sans-serif;margin:0 0 8px}.login-subtitle{text-align:center;color:#83766e;margin:0 0 28px}.login-field{display:block;font-weight:700;color:#665950;margin:16px 0 7px}.login-field input{width:100%;padding:13px 14px;border:1px solid #dfd4cc;border-radius:8px;font:15px "DM Sans"}.login-field input:focus{outline:0;border-color:#f97316;box-shadow:0 0 0 4px #f9731626}.login-button{width:100%;border:0;border-radius:8px;padding:14px;margin-top:24px;background:#f97316;color:white;font:700 15px "DM Sans";cursor:pointer}.login-button:hover{background:#d85c09}.login-error{padding:12px;border-radius:8px;background:#fff0eb;color:#ae412e;text-align:center;font-weight:600;margin-bottom:15px}@media(max-width:480px){.login-card{padding:30px 24px}}}</style></head><body><main class="login-card"><img class="login-logo" src="TITANIO-VERTICAL-FONDO-NEGRO.png" alt="Titanio"><h1>Bienvenido</h1><p class="login-subtitle">Accede al control de proveeduría</p>' . $errorHtml . '<form method="post"><input type="hidden" name="action" value="login"><label class="login-field" for="username">Usuario</label><input class="login-field" id="username" name="username" type="text" autocomplete="username" required><label class="login-field" for="password">Contraseña</label><input class="login-field" id="password" name="password" type="password" autocomplete="current-password" required><button class="login-button" type="submit">Iniciar sesión</button></form></main></body></html>');
}

$currentUserStatement = $pdo->prepare('SELECT * FROM users WHERE id = ? AND active = 1 LIMIT 1');
$currentUserStatement->execute([$_SESSION['user_id']]);
$currentUser = $currentUserStatement->fetch();
if (!$currentUser) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

// Add the sender column when upgrading an existing installation.
$deliverySenderColumn = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'deliveries' AND COLUMN_NAME = 'delivered_by_user_id'")->fetchColumn();
if (!(int) $deliverySenderColumn) {
    $pdo->exec('ALTER TABLE deliveries ADD COLUMN delivered_by_user_id INT UNSIGNED NULL AFTER product_id, ADD CONSTRAINT fk_deliveries_user FOREIGN KEY (delivered_by_user_id) REFERENCES users(id), ADD INDEX idx_deliveries_user (delivered_by_user_id)');
}

$isSuperAdmin = $currentUser['role'] === 'superadmin';
function requirePermission(array $user, string $permission): void
{
    if ($user['role'] !== 'superadmin' && empty($user[$permission])) {
        redirectWithMessage('No tienes permiso para realizar esta acción.', 'error');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if (in_array($action, ['user_create', 'user_update'], true)) {
            if (!$isSuperAdmin) {
                redirectWithMessage('No tienes permisos para administrar usuarios.', 'error');
            }
            $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            $username = trim($_POST['username'] ?? '');
            $fullName = trim($_POST['full_name'] ?? '');
            $role = in_array($_POST['role'] ?? '', ['superadmin', 'admin', 'user'], true) ? $_POST['role'] : 'user';
            if ($username === '' || $fullName === '') {
                redirectWithMessage('Usuario y nombre completo son obligatorios.', 'error');
            }
            $permissions = [
                (int) !empty($_POST['can_deliver']),
                (int) !empty($_POST['can_view_history']),
                (int) !empty($_POST['can_manage_catalog']),
                (int) !empty($_POST['can_manage_users']),
            ];
            if ($role === 'superadmin') {
                $permissions = [1, 1, 1, 1];
            }
            if ($action === 'user_create') {
                $newPassword = $_POST['new_password'] ?? '';
                if ($newPassword === '') {
                    redirectWithMessage('La contraseña es obligatoria para un usuario nuevo.', 'error');
                }
                $statement = $pdo->prepare('INSERT INTO users (username, password_hash, full_name, role, can_deliver, can_view_history, can_manage_catalog, can_manage_users) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $statement->execute([$username, password_hash($newPassword, PASSWORD_DEFAULT), $fullName, $role, ...$permissions]);
            } else {
                $newPassword = $_POST['new_password'] ?? '';
                if ($newPassword !== '') {
                    $statement = $pdo->prepare('UPDATE users SET username = ?, full_name = ?, role = ?, can_deliver = ?, can_view_history = ?, can_manage_catalog = ?, can_manage_users = ?, password_hash = ? WHERE id = ?');
                    $statement->execute([$username, $fullName, $role, ...$permissions, password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
                } else {
                    $statement = $pdo->prepare('UPDATE users SET username = ?, full_name = ?, role = ?, can_deliver = ?, can_view_history = ?, can_manage_catalog = ?, can_manage_users = ? WHERE id = ?');
                    $statement->execute([$username, $fullName, $role, ...$permissions, $userId]);
                }
            }
            redirectWithMessage('Permisos y datos del usuario actualizados.');
        }

        if ($action === 'employee') {
            requirePermission($currentUser, 'can_manage_catalog');
            $name = trim($_POST['name'] ?? '');
            $nationalId = trim($_POST['national_id'] ?? '');
            $department = trim($_POST['department'] ?? '');
            $branch = trim($_POST['branch'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            if ($name === '' || $nationalId === '' || $department === '' || $branch === '') {
                redirectWithMessage('Nombre, cédula, departamento y sucursal son obligatorios.', 'error');
            }
            $statement = $pdo->prepare('INSERT INTO employees (name, national_id, department, branch, phone) VALUES (?, ?, ?, ?, ?)');
            $statement->execute([$name, $nationalId, $department, $branch, $phone ?: null]);
            redirectWithMessage('Empleado registrado correctamente.');
        }

        if ($action === 'employee_update') {
            requirePermission($currentUser, 'can_manage_catalog');
            $employeeId = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
            $name = trim($_POST['name'] ?? '');
            $nationalId = trim($_POST['national_id'] ?? '');
            $department = trim($_POST['department'] ?? '');
            $branch = trim($_POST['branch'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            if (!$employeeId || $name === '' || $nationalId === '' || $department === '' || $branch === '') {
                redirectWithMessage('Completa nombre, cédula, departamento y sucursal.', 'error');
            }
            $statement = $pdo->prepare('UPDATE employees SET name = ?, national_id = ?, department = ?, branch = ?, phone = ? WHERE id = ?');
            $statement->execute([$name, $nationalId, $department, $branch, $phone ?: null, $employeeId]);
            redirectWithMessage('Empleado actualizado correctamente.');
        }

        if ($action === 'employee_delete') {
            requirePermission($currentUser, 'can_manage_catalog');
            $employeeId = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
            if (!$employeeId) {
                redirectWithMessage('Empleado no válido.', 'error');
            }
            $statement = $pdo->prepare('UPDATE employees SET active = 0 WHERE id = ?');
            $statement->execute([$employeeId]);
            redirectWithMessage('Empleado eliminado de los registros activos.');
        }

        if ($action === 'product') {
            requirePermission($currentUser, 'can_manage_catalog');
            $name = trim($_POST['name'] ?? '');
            $sku = trim($_POST['sku'] ?? '');
            if ($name === '' || $sku === '') {
                redirectWithMessage('El nombre y el código de barras o SKU son obligatorios.', 'error');
            }
            $statement = $pdo->prepare('INSERT INTO products (name, sku) VALUES (?, ?)');
            $statement->execute([$name, $sku]);
            redirectWithMessage('Producto registrado correctamente.');
        }

        if ($action === 'product_update') {
            requirePermission($currentUser, 'can_manage_catalog');
            $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            $name = trim($_POST['name'] ?? '');
            $sku = trim($_POST['sku'] ?? '');
            if (!$productId || $name === '' || $sku === '') {
                redirectWithMessage('El nombre y el código de barras o SKU son obligatorios.', 'error');
            }
            $statement = $pdo->prepare('UPDATE products SET name = ?, sku = ? WHERE id = ?');
            $statement->execute([$name, $sku, $productId]);
            redirectWithMessage('Producto actualizado correctamente.');
        }

        if ($action === 'product_delete') {
            requirePermission($currentUser, 'can_manage_catalog');
            $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            if (!$productId) {
                redirectWithMessage('Producto no válido.', 'error');
            }
            $statement = $pdo->prepare('UPDATE products SET active = 0 WHERE id = ?');
            $statement->execute([$productId]);
            redirectWithMessage('Producto eliminado de los registros activos.');
        }

        if ($action === 'delivery') {
            requirePermission($currentUser, 'can_deliver');
            $employeeId = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
            $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
            $deliveryBranch = trim($_POST['delivery_branch'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            if (!$employeeId || !$productId || !$quantity || $quantity < 1 || $deliveryBranch === '') {
                redirectWithMessage('Selecciona empleado, producto, sucursal y una cantidad válida.', 'error');
            }

            $deliveryStatement = $pdo->prepare('INSERT INTO deliveries (employee_id, product_id, delivered_by_user_id, delivery_branch, quantity, notes) VALUES (?, ?, ?, ?, ?, ?)');
            $deliveryStatement->execute([$employeeId, $productId, $currentUser['id'], $deliveryBranch, $quantity, $notes ?: null]);
            redirectWithMessage('Salida registrada correctamente.');
        }
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = $exception->getCode() === '23000' ? 'La cédula o el SKU ya existe. Usa otro valor.' : 'No se pudo guardar la información.';
        redirectWithMessage($message, 'error');
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$view = $_GET['view'] ?? 'dashboard';
$search = trim($_GET['search'] ?? '');
if ($view === 'users' && !$isSuperAdmin) {
    redirectWithMessage('Solo el superadmin puede abrir este módulo.', 'error');
}
if ($view === 'employees' || $view === 'products') {
    requirePermission($currentUser, 'can_manage_catalog');
}
if ($view === 'delivery') {
    requirePermission($currentUser, 'can_deliver');
}
if ($view === 'history') {
    requirePermission($currentUser, 'can_view_history');
}
$managedUsers = $isSuperAdmin ? $pdo->query('SELECT id, username, full_name, role, can_deliver, can_view_history, can_manage_catalog, can_manage_users FROM users WHERE active = 1 ORDER BY username')->fetchAll() : [];
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');
$employeeFilter = filter_input(INPUT_GET, 'employee_id', FILTER_VALIDATE_INT) ?: 0;
$editEmployee = null;
$editProduct = null;
if ($view === 'employees' && !empty($_GET['edit_employee'])) {
    $editStatement = $pdo->prepare('SELECT * FROM employees WHERE id = ? AND active = 1');
    $editStatement->execute([(int) $_GET['edit_employee']]);
    $editEmployee = $editStatement->fetch() ?: null;
}
if ($view === 'products' && !empty($_GET['edit_product'])) {
    $editStatement = $pdo->prepare('SELECT * FROM products WHERE id = ? AND active = 1');
    $editStatement->execute([(int) $_GET['edit_product']]);
    $editProduct = $editStatement->fetch() ?: null;
}

$employees = $pdo->query('SELECT * FROM employees WHERE active = 1 ORDER BY name')->fetchAll();
$products = $pdo->query('SELECT *, 1 AS stock, 0 AS minimum_stock FROM products WHERE active = 1 ORDER BY name')->fetchAll();
$branches = $pdo->query('SELECT DISTINCT branch FROM employees WHERE active = 1 AND branch <> "" ORDER BY branch')->fetchAll(PDO::FETCH_COLUMN);
$stats = [
    'employees' => (int) $pdo->query('SELECT COUNT(*) FROM employees WHERE active = 1')->fetchColumn(),
    'products' => (int) $pdo->query('SELECT COUNT(*) FROM products WHERE active = 1')->fetchColumn(),
    'deliveries' => (int) $pdo->query('SELECT COUNT(*) FROM deliveries')->fetchColumn(),
    'units_delivered' => (int) $pdo->query('SELECT COALESCE(SUM(quantity), 0) FROM deliveries')->fetchColumn(),
    'branches' => (int) $pdo->query('SELECT COUNT(DISTINCT delivery_branch) FROM deliveries')->fetchColumn(),
];

$historyStatement = $pdo->prepare('SELECT d.*, e.name AS employee_name, e.national_id, CONCAT(e.department, " · Sucursal: ", d.delivery_branch) AS department, p.name AS product_name, p.sku, u.full_name AS sender_name FROM deliveries d JOIN employees e ON e.id = d.employee_id JOIN products p ON p.id = d.product_id LEFT JOIN users u ON u.id = d.delivered_by_user_id WHERE (:employee_filter = 0 OR d.employee_id = :employee_filter_value) AND (:search_name = "" OR e.name LIKE :employee_search OR e.national_id LIKE :employee_id_search OR p.name LIKE :product_search OR p.sku LIKE :sku_search OR d.delivery_branch LIKE :branch_search OR u.full_name LIKE :sender_search) AND (:date_from = "" OR d.delivered_at >= :date_from_value) AND (:date_to = "" OR d.delivered_at < DATE_ADD(:date_to_value, INTERVAL 1 DAY)) ORDER BY d.delivered_at DESC LIMIT 100');
$historyStatement->execute([
    'employee_filter' => $employeeFilter,
    'employee_filter_value' => $employeeFilter,
    'search_name' => $search,
    'employee_search' => "%$search%",
    'employee_id_search' => "%$search%",
    'product_search' => "%$search%",
    'sku_search' => "%$search%",
    'branch_search' => "%$search%",
    'sender_search' => "%$search%",
    'date_from' => $dateFrom,
    'date_from_value' => $dateFrom,
    'date_to' => $dateTo,
    'date_to_value' => $dateTo,
]);
$history = $historyStatement->fetchAll();
$lowStockProducts = [];
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Proveeduría | Inventario</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
.logout-link{position:fixed!important;top:18px!important;bottom:auto!important;right:28px!important;z-index:1000;padding:7px 10px;font-size:11px}.session-badge{top:18px;right:150px;padding:5px 10px 5px 6px}.session-avatar{width:24px;height:24px}@media(max-width:620px){.logout-link{top:14px!important;right:16px!important;bottom:auto!important}.session-badge{top:14px;right:122px}}
:root{--ink:#172326;--muted:#6d7b7d;--line:#dce5e3;--paper:#f6f8f5;--white:#fff;--teal:#167d73;--teal-dark:#0e5d56;--coral:#e3735c;--gold:#e4ad4f;--shadow:0 16px 40px rgba(23,35,38,.08)}
*{box-sizing:border-box}body{margin:0;background:var(--paper);color:var(--ink);font:15px 'DM Sans',sans-serif}h1,h2,h3{font-family:'Space Grotesk',sans-serif;margin:0}a{color:inherit;text-decoration:none}.shell{display:flex;min-height:100vh}.sidebar{background:var(--ink);color:#dce8e4;width:250px;padding:28px 18px;display:flex;flex-direction:column}.brand{padding:0 14px 36px}.brand-mark{color:#84d5c6;font-size:13px;letter-spacing:2px;text-transform:uppercase;font-weight:700}.brand h1{color:#fff;font-size:23px;margin-top:7px}.nav-label{font-size:11px;letter-spacing:1.5px;color:#819393;text-transform:uppercase;padding:0 14px 10px}.nav a{display:block;padding:13px 14px;border-radius:8px;margin:3px 0;color:#b8c9c6}.nav a:hover,.nav a.active{background:#294144;color:#fff}.nav a span{display:inline-block;width:25px;color:#84d5c6}.tip{margin:auto 10px 0;padding:17px;background:#234044;border:1px solid #385b5d;border-radius:8px;font-size:12px;color:#b8ccca;line-height:1.5}.main{flex:1;max-width:1500px;padding:34px 42px}.topbar{display:flex;justify-content:space-between;gap:20px;align-items:flex-start;margin-bottom:30px}.eyebrow{color:var(--teal);font-weight:700;font-size:12px;letter-spacing:1.6px;text-transform:uppercase;margin-bottom:8px}.topbar h2{font-size:30px}.date{color:var(--muted);font-size:13px;margin-top:8px}.button{border:0;background:var(--teal);color:#fff;padding:12px 16px;border-radius:7px;font:600 14px 'DM Sans';cursor:pointer}.button:hover{background:var(--teal-dark)}.button.secondary{background:#e5efec;color:var(--teal-dark)}.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-bottom:25px}.stat{background:#fff;border:1px solid var(--line);border-radius:8px;padding:21px;box-shadow:var(--shadow)}.stat p{color:var(--muted);margin:0 0 12px}.stat strong{font:700 30px 'Space Grotesk'}.stat.alert strong{color:var(--coral)}.grid{display:grid;grid-template-columns:1.35fr .85fr;gap:24px}.panel{background:#fff;border:1px solid var(--line);border-radius:8px;padding:23px;box-shadow:var(--shadow);margin-bottom:24px}.panel-head{display:flex;justify-content:space-between;gap:15px;align-items:center;margin-bottom:18px}.panel h3{font-size:18px}.panel-head a{font-size:13px;color:var(--teal);font-weight:700}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:570px}th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);padding:10px 8px;border-bottom:1px solid var(--line)}td{padding:14px 8px;border-bottom:1px solid #edf1ef}tr:last-child td{border:0}.person{font-weight:700}.sub{color:var(--muted);font-size:12px;margin-top:3px}.pill{display:inline-block;padding:5px 9px;border-radius:20px;background:#e8f4f0;color:var(--teal-dark);font-size:12px;font-weight:700}.pill.low{background:#fff0e9;color:#bd593f}.empty{color:var(--muted);padding:25px 8px;text-align:center}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.field{display:flex;flex-direction:column;gap:6px}.field.full{grid-column:1/-1}.field label{font-size:12px;color:var(--muted);font-weight:700}.field input,.field select,.field textarea{font:14px 'DM Sans';border:1px solid var(--line);border-radius:6px;padding:11px;background:#fbfcfb;color:var(--ink);width:100%}.field textarea{resize:vertical}.form-actions{margin-top:16px;display:flex;justify-content:flex-end}.search{display:flex;gap:8px;margin-bottom:14px}.search input{flex:1;border:1px solid var(--line);border-radius:6px;padding:11px;font:14px 'DM Sans'}.flash{padding:13px 16px;border-radius:7px;margin-bottom:20px;font-weight:600}.flash.success{background:#e4f3ed;color:#14675d}.flash.error{background:#fff0eb;color:#a84635}.quick{display:grid;grid-template-columns:1fr 1fr;gap:10px}.quick a{border:1px solid var(--line);padding:14px;border-radius:6px;color:var(--teal-dark);font-weight:700;font-size:13px}.quick a:hover{border-color:var(--teal)}@media(max-width:900px){.sidebar{width:205px}.main{padding:25px}.stats{grid-template-columns:1fr 1fr}.grid{grid-template-columns:1fr}}@media(max-width:620px){.shell{display:block}.sidebar{width:100%;padding:18px}.brand{padding:0 8px 17px}.nav{display:flex;overflow:auto;gap:4px}.nav-label,.tip{display:none}.nav a{white-space:nowrap;padding:10px}.main{padding:22px 16px}.topbar{display:block}.topbar .button{margin-top:15px}.topbar h2{font-size:26px}.stats{gap:9px}.stat{padding:15px}.stat strong{font-size:24px}.stat p{font-size:12px}}
.search-select{position:relative}.search-select::after{content:'⌄';position:absolute;right:13px;top:8px;color:var(--muted);font-size:19px;pointer-events:none}.search-select input{padding-right:34px}.combobox-options{display:none;position:absolute;z-index:5;left:0;right:0;top:calc(100% + 4px);max-height:210px;overflow-y:auto;background:#fff;border:1px solid var(--line);border-radius:6px;box-shadow:var(--shadow)}.combobox-options.open{display:block}.combobox-option{padding:11px 13px;border-bottom:1px solid #edf1ef;cursor:pointer;font-size:13px}.combobox-option:last-child{border-bottom:0}.combobox-option:hover{background:#e8f4f0;color:var(--teal-dark)}.delete-button{border:0;background:none;color:#bd593f;font:600 13px 'DM Sans';cursor:pointer;margin-left:8px;padding:0}.delete-button:hover{text-decoration:underline}
.main{background:radial-gradient(circle at 88% 4%,rgba(249,115,22,.13),transparent 24rem),linear-gradient(135deg,#fffdfb 0%,#f4f1ee 100%);min-height:100vh}.sidebar{background:linear-gradient(165deg,#242323 0%,#302d2c 65%,#191919 100%);box-shadow:12px 0 35px rgba(36,35,35,.18)}.brand-logo{display:block;width:100%;height:auto;max-height:170px;object-fit:contain;margin:0 auto 18px;border-radius:6px}.brand-mark{color:#ff8a2b}.nav a{border:1px solid transparent;transition:.2s ease}.nav a:hover,.nav a.active{background:rgba(249,115,22,.16);border-color:rgba(249,115,22,.3);color:#fff;transform:translateX(3px)}.topbar{align-items:center}.topbar h2{font-size:34px;letter-spacing:0;color:#292525}.date{color:#776f6a}.button{display:inline-flex;align-items:center;gap:8px;border-radius:8px;padding:12px 18px;background:#f97316;box-shadow:0 7px 16px rgba(249,115,22,.22);transition:.2s ease}.button:hover{background:#d95b08;transform:translateY(-2px);box-shadow:0 10px 20px rgba(249,115,22,.27)}.stats{gap:18px}.stat{position:relative;overflow:hidden;border:0;border-left:4px solid #f97316;box-shadow:0 12px 30px rgba(36,35,35,.08);transition:.2s ease}.stat:hover{transform:translateY(-3px);box-shadow:0 18px 35px rgba(36,35,35,.12)}.stat:nth-child(2){border-left-color:#f6b544}.stat:nth-child(3){border-left-color:#df6048}.stat:nth-child(4){border-left-color:#e88934}.stat strong{color:#342d29}.panel{border:0;box-shadow:0 12px 30px rgba(36,35,35,.08);padding:26px}.panel-head{border-bottom:1px solid #eee7e2;padding-bottom:15px}.panel h3{color:#342d29}.field input,.field select,.field textarea,.search input{border-color:#e2d9d2;background:#fffdfb;transition:.2s ease}.field input:focus,.field select:focus,.field textarea:focus,.search input:focus{outline:0;border-color:#f08a45;box-shadow:0 0 0 4px rgba(249,115,22,.14)}th{color:#806f65;font-weight:700}td{color:#443b36}.pill{border-radius:6px}.quick a{background:#fffaf6;transition:.2s ease}.quick a:hover{background:#fff0e5;transform:translateY(-2px)}.combobox-options{border:0;box-shadow:0 15px 30px rgba(36,35,35,.16)}.combobox-option{padding:13px 14px}.flash{box-shadow:0 8px 20px rgba(36,35,35,.06)}.delete-button{border-radius:6px;padding:4px 8px;background:#fff0e9}.delete-button:hover{text-decoration:none;background:#ffe0d2}.search{align-items:center}.search input{background:#fff}@media(max-width:620px){.topbar h2{font-size:26px}.brand-logo{max-height:130px}}
.logout-link{position:fixed;right:28px;bottom:24px;color:#8b3d22;background:#fff3ec;border:1px solid #f4c8b2;border-radius:8px;padding:9px 13px;font-size:12px;font-weight:700;box-shadow:0 8px 18px rgba(36,35,35,.08)}.logout-link:hover{background:#f97316;color:#fff;border-color:#f97316}.session-badge{position:absolute;right:30px;top:20px;display:flex;align-items:center;gap:9px;color:#665950;background:rgba(255,255,255,.8);border:1px solid #eadfd7;border-radius:30px;padding:7px 12px 7px 8px;font-size:12px;font-weight:700}.session-avatar{display:grid;place-items:center;width:27px;height:27px;border-radius:50%;background:#f97316;color:#fff;font-size:13px}.main{position:relative}.topbar{padding-top:35px}.panel,.stat{backdrop-filter:blur(8px)}.panel-head a:not(.button){font-weight:700}.brand-logo{box-shadow:0 10px 26px rgba(0,0,0,.22)}.permission-list{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:18px 0}.permission-list label{display:flex;align-items:center;gap:8px;padding:11px;background:#fff8f3;border:1px solid #f0dfd3;border-radius:8px;font-size:13px}.permission-list input{accent-color:#f97316}.role-select{width:100%;padding:12px;border:1px solid #e2d9d2;border-radius:8px;background:#fffdfb}.user-table td{vertical-align:middle}.role-badge{display:inline-block;padding:5px 9px;border-radius:20px;background:#fff0e5;color:#ba560c;font-size:11px;font-weight:700}@media(max-width:620px){.permission-list{grid-template-columns:1fr}.session-badge{right:16px;top:15px}}
.logout-link{position:fixed;right:28px;bottom:24px;color:#8b3d22;background:#fff3ec;border:1px solid #f4c8b2;border-radius:8px;padding:9px 13px;font-size:12px;font-weight:700;box-shadow:0 8px 18px rgba(36,35,35,.08)}.logout-link:hover{background:#f97316;color:#fff;border-color:#f97316}.session-badge{position:absolute;right:30px;top:20px;display:flex;align-items:center;gap:9px;color:#665950;background:rgba(255,255,255,.8);border:1px solid #eadfd7;border-radius:30px;padding:7px 12px 7px 8px;font-size:12px;font-weight:700}.session-avatar{display:grid;place-items:center;width:27px;height:27px;border-radius:50%;background:#f97316;color:#fff;font-size:13px}.main{position:relative}.topbar{padding-top:35px}.panel,.stat{backdrop-filter:blur(8px)}.panel-head a:not(.button){font-weight:700}.brand-logo{box-shadow:0 10px 26px rgba(0,0,0,.22)}.permission-list{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:18px 0}.permission-list label{display:flex;align-items:center;gap:8px;padding:11px;background:#fff8f3;border:1px solid #f0dfd3;border-radius:8px;font-size:13px}.permission-list input{accent-color:#f97316}.role-select{width:100%;padding:12px;border:1px solid #e2d9d2;border-radius:8px;background:#fffdfb}.user-table td{vertical-align:middle}.role-badge{display:inline-block;padding:5px 9px;border-radius:20px;background:#fff0e5;color:#ba560c;font-size:11px;font-weight:700}.actions-cell{width:145px;white-space:nowrap}.row-actions{display:flex;align-items:center;justify-content:center;gap:7px}.view-history-button,.edit-action,.delete-button{display:inline-flex;align-items:center;justify-content:center;height:32px;border-radius:7px;margin:0;font:700 12px 'DM Sans';cursor:pointer;text-decoration:none}.view-history-button{width:32px;background:#fff3e9;color:#d85c09}.view-history-button:hover{background:#f97316;color:#fff}.edit-action{padding:0 10px;background:#f0f5f3;color:#356c62}.edit-action:hover{background:#dcebe6}.delete-button{padding:0 10px;background:#fff0e9;color:#bd593f}.delete-button:hover{text-decoration:none;background:#f9d9cf}@media(max-width:620px){.permission-list{grid-template-columns:1fr}.session-badge{right:16px;top:15px}.actions-cell{width:120px}.row-actions{gap:4px}.edit-action,.delete-button{padding:0 7px}}
.site-credit{position:fixed;left:250px;right:180px;bottom:14px;z-index:20;text-align:center;color:#806f65;font-size:12px;font-weight:700;letter-spacing:.6px;pointer-events:none}.site-credit strong{color:#d85c09}@media(max-width:620px){.site-credit{left:0;right:0;bottom:10px;font-size:11px}}
.logout-link{top:18px;bottom:auto;right:28px;z-index:30;padding:7px 10px;font-size:11px}.session-badge{top:18px;right:150px;padding:5px 10px 5px 6px;font-size:11px}.session-avatar{width:24px;height:24px}@media(max-width:620px){.logout-link{top:14px;right:16px;bottom:auto}.session-badge{top:14px;right:122px}.site-credit{bottom:10px}}.brand-logo{max-height:150px!important;object-fit:contain}
.logout-link{position:fixed!important;top:12px!important;right:20px!important;bottom:auto!important;left:auto!important;margin:0!important;transform:none!important;z-index:9999!important;display:block!important}.site-credit{position:fixed!important;bottom:12px!important;top:auto!important}
@media(max-width:620px){.logout-link{top:10px!important;right:12px!important}.site-credit{bottom:8px!important}}
</style>
</head>
<body>
<div class="shell">
<aside class="sidebar"><div class="brand"><img class="brand-logo" src="TITANIO-VERTICAL-FONDO-NEGRO.png" alt="Titanio"><div class="brand-mark">Control interno</div><h1>Proveeduría</h1></div><div class="nav-label">Menú principal</div><nav class="nav"><a class="<?= $view === 'dashboard' ? 'active' : '' ?>" href="index.php"><span>⌂</span>Resumen</a><a class="<?= $view === 'delivery' ? 'active' : '' ?>" href="?view=delivery"><span>＋</span>Registrar entrega</a><a class="<?= $view === 'history' ? 'active' : '' ?>" href="?view=history"><span>↗</span>Historial</a><a class="<?= $view === 'employees' ? 'active' : '' ?>" href="?view=employees"><span>◉</span>Empleados</a><a class="<?= $view === 'products' ? 'active' : '' ?>" href="?view=products"><span>▦</span>Inventario</a></nav><div class="tip"><strong>Orden del día</strong><br>Registra cada salida al momento de entregar para mantener existencias confiables.</div></aside>
<main class="main"><header class="topbar"><div><div class="eyebrow">Panel de control</div><h2><?= $view === 'dashboard' ? 'Resumen de proveeduría' : ($view === 'delivery' ? 'Registrar una entrega' : ($view === 'history' ? 'Historial de entregas' : ($view === 'employees' ? 'Directorio de empleados' : 'Inventario de productos'))) ?></h2><div class="date"><?= spanishDate() ?></div></div><?php if ($view !== 'delivery'): ?><a class="button" href="?view=delivery">＋ Nueva entrega</a><?php endif; ?></header>
<?php if ($flash): ?><div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<?php if ($view === 'dashboard'): ?>
<section class="stats"><div class="stat"><p>Empleados activos</p><strong><?= $stats['employees'] ?></strong></div><div class="stat"><p>Productos registrados</p><strong><?= $stats['products'] ?></strong></div><div class="stat"><p>Entregas realizadas</p><strong><?= $stats['deliveries'] ?></strong></div><div class="stat alert"><p>Stock por revisar</p><strong><?= $stats['low_stock'] ?></strong></div></section>
<div class="grid"><section class="panel"><div class="panel-head"><h3>Últimas entregas</h3><a href="?view=history">Ver historial completo →</a></div><?php if (!$history): ?><div class="empty">Aún no hay entregas registradas.</div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Persona</th><th>Producto</th><th>Cantidad</th><th>Fecha</th></tr></thead><tbody><?php foreach (array_slice($history, 0, 6) as $item): ?><tr><td><div class="person"><?= e($item['employee_name']) ?></div><div class="sub"><?= e($item['department']) ?></div></td><td><?= e($item['product_name']) ?><div class="sub"><?= e($item['sku']) ?></div></td><td><span class="pill"><?= e($item['quantity']) ?> un.</span></td><td class="sub"><?= date('d/m/Y H:i', strtotime($item['delivered_at'])) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<div><section class="panel"><div class="panel-head"><h3>Stock por revisar</h3><a href="?view=products">Ver inventario</a></div><?php if (!$lowStockProducts): ?><div class="empty">Todo el stock está en buen nivel.</div><?php else: ?><?php foreach (array_slice($lowStockProducts, 0, 5) as $product): ?><div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #edf1ef"><div><strong><?= e($product['name']) ?></strong><div class="sub">Mínimo: <?= e($product['minimum_stock']) ?></div></div><span class="pill low"><?= e($product['stock']) ?> un.</span></div><?php endforeach; ?><?php endif; ?></section><section class="panel"><div class="panel-head"><h3>Acciones rápidas</h3></div><div class="quick"><a href="?view=employees">＋ Empleado</a><a href="?view=products">＋ Producto</a></div></section></div></div>
<?php elseif ($view === 'legacy-delivery'): ?><section class="panel" style="max-width:760px"><div class="panel-head"><h3>Datos de la salida</h3></div><form method="post"><input type="hidden" name="action" value="delivery"><div class="form-grid"><div class="field"><label for="employee_id">Empleado *</label><select id="employee_id" name="employee_id" required><option value="">Selecciona una persona</option><?php foreach ($employees as $employee): ?><option value="<?= $employee['id'] ?>"><?= e($employee['name']) ?> · <?= e($employee['department']) ?></option><?php endforeach; ?></select></div><div class="field"><label for="product_id">Producto *</label><select id="product_id" name="product_id" required><option value="">Selecciona un producto</option><?php foreach ($products as $product): ?><option value="<?= $product['id'] ?>" <?= $product['stock'] < 1 ? 'disabled' : '' ?>><?= e($product['name']) ?> · <?= e($product['stock']) ?> disponibles</option><?php endforeach; ?></select></div><div class="field"><label for="quantity">Cantidad *</label><input id="quantity" name="quantity" type="number" min="1" required></div><div class="field full"><label for="notes">Observaciones</label><textarea id="notes" name="notes" rows="3" placeholder="Ej. Entrega correspondiente al mes de septiembre"></textarea></div></div><div class="form-actions"><button class="button" type="submit">Guardar entrega</button></div></form></section>
<?php elseif ($view === 'delivery'): ?><section class="panel" style="max-width:760px"><div class="panel-head"><h3>Datos de la salida</h3></div><form method="post" id="delivery-form"><input type="hidden" name="action" value="delivery"><div class="form-grid"><div class="field"><label for="employee_search">Empleado *</label><div class="search-select"><input id="employee_search" list="employee-options" autocomplete="off" placeholder="Escribe nombre o cédula..." required><input type="hidden" id="employee_id" name="employee_id"><datalist id="employee-options"><?php foreach ($employees as $employee): ?><option value="<?= e($employee['name']) ?> · Cédula <?= e($employee['national_id']) ?> · <?= e($employee['department']) ?> · <?= e($employee['branch']) ?>" data-employee-id="<?= $employee['id'] ?>"></option><?php endforeach; ?></datalist></div><small>Busca por nombre, cédula, departamento o sucursal.</small></div><div class="field"><label for="product_search">Producto *</label><div class="search-select"><input id="product_search" list="product-options" autocomplete="off" placeholder="Escribe para buscar..." required><input type="hidden" id="product_id" name="product_id"><datalist id="product-options"><?php foreach ($products as $product): if ($product['stock'] > 0): ?><option value="<?= e($product['name']) ?> · <?= e($product['sku']) ?> (<?= e($product['stock']) ?> disponibles)" data-product-id="<?= $product['id'] ?>"></option><?php endif; endforeach; ?></datalist></div><small>Busca por nombre o código SKU.</small></div><div class="field"><label for="quantity">Cantidad *</label><input id="quantity" name="quantity" type="number" min="1" required></div><div class="field full"><label for="notes">Observaciones</label><textarea id="notes" name="notes" rows="3" placeholder="Ej. Entrega correspondiente al mes de septiembre"></textarea></div></div><div class="form-actions"><button class="button" type="submit">Guardar entrega</button></div></form></section>
<?php elseif ($view === 'employees'): ?><section class="grid"><section class="panel"><div class="panel-head"><h3>Empleados registrados</h3></div><?php if (!$employees): ?><div class="empty">Agrega el primer empleado usando el formulario.</div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Nombre</th><th>Cédula</th><th>Departamento</th><th>Sucursal</th><th>Teléfono</th><th></th></tr></thead><tbody><?php foreach ($employees as $employee): ?><tr><td class="person"><?= e($employee['name']) ?></td><td><?= e($employee['national_id']) ?></td><td><?= e($employee['department']) ?></td><td><?= e($employee['branch']) ?></td><td class="sub"><?= e($employee['phone'] ?: 'Sin teléfono') ?></td><td><a class="panel-head a" href="?view=employees&edit_employee=<?= $employee['id'] ?>">Editar</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section><section class="panel"><div class="panel-head"><h3><?= $editEmployee ? 'Editar empleado' : 'Nuevo empleado' ?></h3><?php if ($editEmployee): ?><a href="?view=employees">Cancelar</a><?php endif; ?></div><form method="post"><input type="hidden" name="action" value="<?= $editEmployee ? 'employee_update' : 'employee' ?>"><?php if ($editEmployee): ?><input type="hidden" name="employee_id" value="<?= $editEmployee['id'] ?>"><?php endif; ?><div class="form-grid"><div class="field full"><label for="name">Nombre completo *</label><input id="name" name="name" value="<?= e($editEmployee['name'] ?? '') ?>" required></div><div class="field full"><label for="national_id">Cédula *</label><input id="national_id" name="national_id" value="<?= e($editEmployee['national_id'] ?? '') ?>" maxlength="30" required></div><div class="field"><label for="department">Departamento *</label><input id="department" name="department" value="<?= e($editEmployee['department'] ?? '') ?>" required></div><div class="field"><label for="branch">Sucursal *</label><input id="branch" name="branch" value="<?= e($editEmployee['branch'] ?? '') ?>" required></div><div class="field full"><label for="phone">Teléfono</label><input id="phone" name="phone" value="<?= e($editEmployee['phone'] ?? '') ?>"></div></div><div class="form-actions"><button class="button" type="submit"><?= $editEmployee ? 'Guardar cambios' : 'Registrar empleado' ?></button></div></form></section></section>
<?php elseif ($view === 'products'): ?><section class="grid"><section class="panel"><div class="panel-head"><h3>Existencias actuales</h3></div><div class="table-wrap"><table><thead><tr><th>Producto</th><th>SKU</th><th>Disponibles</th><th>Estado</th><th></th></tr></thead><tbody><?php foreach ($products as $product): ?><tr><td class="person"><?= e($product['name']) ?></td><td class="sub"><?= e($product['sku']) ?></td><td><?= e($product['stock']) ?> unidades</td><td><span class="pill <?= $product['stock'] <= $product['minimum_stock'] ? 'low' : '' ?>"><?= $product['stock'] <= $product['minimum_stock'] ? 'Reponer' : 'Disponible' ?></span></td><td><a class="panel-head a" href="?view=products&edit_product=<?= $product['id'] ?>">Editar</a></td></tr><?php endforeach; ?></tbody></table></div></section><section class="panel"><div class="panel-head"><h3><?= $editProduct ? 'Editar producto' : 'Nuevo producto' ?></h3><?php if ($editProduct): ?><a href="?view=products">Cancelar</a><?php endif; ?></div><form method="post"><input type="hidden" name="action" value="<?= $editProduct ? 'product_update' : 'product' ?>"><?php if ($editProduct): ?><input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>"><?php endif; ?><div class="form-grid"><div class="field full"><label for="product_name">Nombre del producto *</label><input id="product_name" name="name" value="<?= e($editProduct['name'] ?? '') ?>" required></div><div class="field"><label for="sku">SKU / Código *</label><input id="sku" name="sku" value="<?= e($editProduct['sku'] ?? '') ?>" required></div><div class="field"><label for="stock">Existencias *</label><input id="stock" name="stock" value="<?= e($editProduct['stock'] ?? '') ?>" type="number" min="0" required></div><div class="field full"><label for="minimum_stock">Avisar cuando queden</label><input id="minimum_stock" name="minimum_stock" value="<?= e($editProduct['minimum_stock'] ?? 5) ?>" type="number" min="0" required></div></div><div class="form-actions"><button class="button" type="submit"><?= $editProduct ? 'Guardar cambios' : 'Agregar producto' ?></button></div></form></section></section>
<?php elseif ($view === 'history'): ?><section class="panel"><div class="panel-head"><h3><?= count($history) ?> movimientos recientes</h3></div><form class="search" method="get"><input type="hidden" name="view" value="history"><input name="search" value="<?= e($search) ?>" placeholder="Buscar por empleado, producto, SKU o quien envió"><button class="button" type="submit">Buscar</button></form><div class="table-wrap"><table><thead><tr><th>Fecha</th><th>Empleado</th><th>Producto</th><th>Cantidad</th><th>Enviado por</th><th>Observaciones</th></tr></thead><tbody><?php if (!$history): ?><tr><td class="empty" colspan="6">No encontramos movimientos.</td></tr><?php else: foreach ($history as $item): ?><tr><td class="sub"><?= date('d/m/Y H:i', strtotime($item['delivered_at'])) ?></td><td><div class="person"><?= e($item['employee_name']) ?></div><div class="sub"><?= e($item['department']) ?></div></td><td><div class="person"><?= e($item['product_name']) ?></div><div class="sub"><?= e($item['sku']) ?></div></td><td><span class="pill"><?= e($item['quantity']) ?> un.</span></td><td><div class="person"><?= e($item['sender_name'] ?: 'Usuario anterior') ?></div></td><td class="sub"><?= e($item['notes'] ?: '—') ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section><?php endif; ?>
</main></div>
<a class="logout-link" href="?action=logout">Cerrar sesión</a>
<?php if ($view === 'delivery'): ?><script>
const productSearch = document.getElementById('product_search');
const deliveryForm = document.getElementById('delivery-form');
const productField = productSearch.closest('.field');
const branchField = document.createElement('div');
branchField.className = 'field';
branchField.innerHTML = '<label for="delivery_branch">Sucursal de entrega *</label><div class="search-select"><input id="delivery_branch" name="delivery_branch" list="branch-options" autocomplete="off" placeholder="Escribe o selecciona la sucursal..." required><datalist id="branch-options"><?php foreach ($branches as $branch): ?><option value="<?= e($branch) ?>"></option><?php endforeach; ?></datalist></div><small>Se guardará la sucursal de este momento.</small>';
productField.parentElement.insertBefore(branchField, productField);
const productId = document.getElementById('product_id');
const productOptions = Array.from(document.querySelectorAll('#product-options option'));
const employeeSearch = document.getElementById('employee_search');
const employeeId = document.getElementById('employee_id');
const employeeOptions = Array.from(document.querySelectorAll('#employee-options option'));
employeeSearch.addEventListener('input', () => {
    const selected = employeeOptions.find(option => option.value === employeeSearch.value);
    employeeId.value = selected ? selected.dataset.employeeId : '';
});
productSearch.addEventListener('input', () => {
    const selected = productOptions.find(option => option.value === productSearch.value);
    productId.value = selected ? selected.dataset.productId : '';
});
document.getElementById('delivery-form').addEventListener('submit', event => {
    if (!employeeId.value || !productId.value) {
        event.preventDefault();
        const invalidField = !employeeId.value ? employeeSearch : productSearch;
        invalidField.setCustomValidity(!employeeId.value ? 'Selecciona un empleado de la lista.' : 'Selecciona un producto de la lista.');
        invalidField.reportValidity();
    } else {
        employeeSearch.setCustomValidity('');
        productSearch.setCustomValidity('');
    }
});
document.querySelector('#delivery-form button[type="submit"]').addEventListener('click', event => {
    if (!employeeId.value || !productId.value) {
        event.preventDefault();
        const invalidField = !employeeId.value ? employeeSearch : productSearch;
        invalidField.setCustomValidity(!employeeId.value ? 'Selecciona un empleado de la lista.' : 'Selecciona un producto de la lista.');
        invalidField.reportValidity();
    }
});

function setupCombobox(input, list, hidden, dataKey) {
    input.removeAttribute('list');
    const optionsBox = document.createElement('div');
    optionsBox.className = 'combobox-options';
    input.parentElement.appendChild(optionsBox);
    const sourceOptions = Array.from(list.options);
    if (input.id === 'product_search') {
        sourceOptions.forEach(option => {
            option.value = option.value.replace(/ \(\d+ disponibles\)$/, '');
        });
    }

    const renderOptions = () => {
        const query = input.value.toLowerCase().trim();
        optionsBox.innerHTML = '';
        sourceOptions.filter(option => option.value.toLowerCase().includes(query)).forEach(option => {
            const item = document.createElement('div');
            item.className = 'combobox-option';
            item.textContent = option.value;
            item.addEventListener('click', () => {
                input.value = option.value;
                hidden.value = option.dataset[dataKey];
                input.setCustomValidity('');
                optionsBox.classList.remove('open');
            });
            optionsBox.appendChild(item);
        });
        optionsBox.classList.toggle('open', optionsBox.children.length > 0);
    };

    input.addEventListener('focus', renderOptions);
    input.addEventListener('input', renderOptions);
    document.addEventListener('click', event => {
        if (!input.parentElement.contains(event.target)) optionsBox.classList.remove('open');
    });
}

setupCombobox(employeeSearch, document.getElementById('employee-options'), employeeId, 'employeeId');
setupCombobox(productSearch, document.getElementById('product-options'), productId, 'productId');
</script><?php endif; ?>
<?php if ($view === 'products'): ?><script>
const skuInput = document.getElementById('sku');
if (skuInput) {
    skuInput.required = true;
    skuInput.maxLength = 50;
    skuInput.previousElementSibling.textContent = 'Código de barras / SKU *';
}
document.querySelectorAll('.field').forEach(field => {
    const label = field.querySelector('label');
    if (label && /Existencias|Avisar cuando/.test(label.textContent)) field.remove();
});
const productTable = document.querySelector('.table-wrap table');
if (productTable) {
    const stockColumn = Array.from(productTable.querySelectorAll('th')).findIndex(th => th.textContent.trim() === 'Disponibles');
    if (stockColumn >= 0) productTable.querySelectorAll('tr').forEach(row => row.children[stockColumn]?.remove());
    const statusColumn = Array.from(productTable.querySelectorAll('th')).findIndex(th => th.textContent.trim() === 'Estado');
    if (statusColumn >= 0) productTable.querySelectorAll('tr').forEach(row => row.children[statusColumn]?.remove());
}
</script><?php endif; ?>
<?php if ($view === 'dashboard'): ?><script>
const unitsCard = document.querySelector('.stat.alert');
if (unitsCard) {
    unitsCard.classList.remove('alert');
    unitsCard.querySelector('p').textContent = 'Unidades entregadas';
    unitsCard.querySelector('strong').textContent = <?= json_encode($stats['units_delivered']) ?>;
}
document.querySelectorAll('.panel').forEach(panel => {
    if (panel.querySelector('h3')?.textContent.trim() === 'Stock por revisar') {
        panel.querySelector('h3').textContent = 'Sucursales con entregas';
        panel.querySelector('.panel-head a').remove();
        panel.querySelector('.empty').textContent = <?= json_encode($stats['branches']) ?> + ' sucursal(es) registradas en las salidas.';
    }
});
</script><?php endif; ?>
<?php if ($view === 'history'): ?><script>
const historyForm = document.querySelector('.search');
const historySearch = historyForm?.querySelector('input[name="search"]');
if (historyForm && historySearch) {
    historySearch.placeholder = 'Persona, cédula, producto, SKU o sucursal';
    const dateFrom = document.createElement('input');
    dateFrom.type = 'date';
    dateFrom.name = 'date_from';
    dateFrom.value = <?= json_encode($dateFrom) ?>;
    dateFrom.title = 'Fecha desde';
    const dateTo = document.createElement('input');
    dateTo.type = 'date';
    dateTo.name = 'date_to';
    dateTo.value = <?= json_encode($dateTo) ?>;
    dateTo.title = 'Fecha hasta';
    historyForm.insertBefore(dateFrom, historyForm.querySelector('button'));
    historyForm.insertBefore(dateTo, historyForm.querySelector('button'));
}
</script><?php endif; ?>
<?php if ($view === 'employees' || $view === 'products'): ?><script>
const recordsTable = document.querySelector('.table-wrap table');
const recordType = <?= json_encode($view === 'employees' ? 'employee' : 'product') ?>;
if (recordsTable) {
    recordsTable.querySelectorAll('tbody tr').forEach(row => {
        const editLink = row.querySelector('a[href*="edit_"]');
        if (!editLink) return;
        const id = new URL(editLink.href).searchParams.get(recordType === 'employee' ? 'edit_employee' : 'edit_product');
        const action = recordType + '_delete';
        const idField = recordType + '_id';
        const actionCell = editLink.parentElement;
        actionCell.className = 'actions-cell';
        const actions = document.createElement('div');
        actions.className = 'row-actions';
        actionCell.innerHTML = '';
        actionCell.appendChild(actions);
        if (recordType === 'employee') {
            const historyLink = document.createElement('a');
            historyLink.className = 'view-history-button';
            historyLink.href = '?view=history&employee_id=' + encodeURIComponent(id);
            historyLink.title = 'Ver historial de esta persona';
            historyLink.setAttribute('aria-label', 'Ver historial de esta persona');
            historyLink.innerHTML = '<i class="bi bi-eye-fill"></i>';
            actions.appendChild(historyLink);
        }
        editLink.className = 'edit-action';
        editLink.innerHTML = '<i class="bi bi-pencil-square"></i> Editar';
        actions.appendChild(editLink);
        const form = document.createElement('form');
        form.method = 'post';
        form.style.display = 'inline';
        form.innerHTML = '<input type="hidden" name="action" value="' + action + '"><input type="hidden" name="' + idField + '" value="' + id + '"><button class="delete-button" type="submit">Borrar</button>';
        form.addEventListener('submit', event => {
            if (!confirm('¿Seguro que deseas borrar este registro? Se conservará en el historial.')) event.preventDefault();
        });
        actions.appendChild(form);
    });
}
</script><?php endif; ?>
<script>const mainArea = document.querySelector('.main'); if (mainArea) { const badge = document.createElement('div'); badge.className = 'session-badge'; badge.innerHTML = '<span class="session-avatar"><i class="bi bi-person-fill"></i></span><?= e($_SESSION['user_name'] ?? 'Usuario') ?>'; mainArea.prepend(badge); }</script>
<script>const companyLogo = document.querySelector('.brand-logo'); if (companyLogo) { companyLogo.src = 'Gemini_Generated_Image_h43kklh43kklh43k.jpg'; companyLogo.alt = 'Proveeduría Titanio'; }</script>
<?php if ($isSuperAdmin): ?><script>
const adminNav = document.querySelector('.nav');
const usersLink = document.createElement('a');
usersLink.href = '?view=users';
usersLink.innerHTML = '<span>⚙</span>Usuarios y permisos';
if (<?= json_encode($view === 'users') ?>) usersLink.classList.add('active');
adminNav?.appendChild(usersLink);

if (<?= json_encode($view === 'users') ?>) {
    const main = document.querySelector('.main');
    const users = <?= json_encode($managedUsers, JSON_UNESCAPED_UNICODE) ?>;
    main.innerHTML = '<header class="topbar"><div><div class="eyebrow">Administración</div><h2>Usuarios y permisos</h2><div class="date">Control de acceso del sistema</div></div></header><div class="grid"><section class="panel"><div class="panel-head"><h3>Usuarios activos</h3><span class="role-badge">Solo superadmin</span></div><div class="table-wrap"><table class="user-table"><thead><tr><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Permisos</th><th></th></tr></thead><tbody id="users-body"></tbody></table></div></section><section class="panel"><div class="panel-head"><h3 id="user-form-title">Nuevo usuario</h3><a id="cancel-user-edit" href="?view=users" style="display:none">Cancelar</a></div><form method="post" id="user-form"><input type="hidden" name="action" id="user-action" value="user_create"><input type="hidden" name="user_id" id="user-id"><div class="field"><label for="username">Usuario *</label><input id="username" name="username" required></div><div class="field"><label for="full_name">Nombre completo *</label><input id="full_name" name="full_name" required></div><div class="field"><label for="role">Rol *</label><select class="role-select" id="role" name="role"><option value="user">Usuario</option><option value="admin">Administrador</option><option value="superadmin">Superadmin</option></select></div><div class="field"><label for="new_password">Contraseña</label><input id="new_password" name="new_password" type="password" minlength="8" placeholder="Obligatoria al crear"></div><div class="permission-list"><label><input type="checkbox" name="can_deliver" value="1" checked> Registrar salidas</label><label><input type="checkbox" name="can_view_history" value="1" checked> Ver historial</label><label><input type="checkbox" name="can_manage_catalog" value="1"> Gestionar catálogo</label><label><input type="checkbox" name="can_manage_users" value="1"> Gestionar usuarios</label></div><button class="button" type="submit">Guardar permisos</button></form></section></div><a class="logout-link" href="?action=logout">Cerrar sesión</a>';
    const usersBody = document.getElementById('users-body');
    const form = document.getElementById('user-form');
    const setField = (id, value) => { document.getElementById(id).value = value || ''; };
    users.forEach(user => {
        const row = document.createElement('tr');
        const permissions = ['can_deliver', 'can_view_history', 'can_manage_catalog', 'can_manage_users'].filter(permission => Number(user[permission])).length;
        row.innerHTML = '<td><strong></strong></td><td></td><td><span class="role-badge"></span></td><td>' + permissions + ' permisos</td><td><button type="button" class="button secondary edit-user">Editar</button></td>';
        row.children[0].querySelector('strong').textContent = user.username;
        row.children[1].textContent = user.full_name;
        row.children[2].querySelector('span').textContent = user.role;
        row.querySelector('.edit-user').addEventListener('click', () => {
            setField('user-id', user.id); setField('username', user.username); setField('full_name', user.full_name); setField('role', user.role); setField('new_password', '');
            ['can_deliver', 'can_view_history', 'can_manage_catalog', 'can_manage_users'].forEach(permission => { form.querySelector('[name="' + permission + '"]').checked = Number(user[permission]) === 1; });
            document.getElementById('user-action').value = 'user_update'; document.getElementById('user-form-title').textContent = 'Editar usuario'; document.getElementById('cancel-user-edit').style.display = 'inline';
        });
        usersBody.appendChild(row);
    });
}
</script><?php endif; ?>
<script>
const permissions = <?= json_encode(['deliver' => (bool) $currentUser['can_deliver'], 'history' => (bool) $currentUser['can_view_history'], 'catalog' => (bool) $currentUser['can_manage_catalog'], 'superadmin' => $isSuperAdmin]) ?>;
if (!permissions.superadmin) {
    if (!permissions.deliver) document.querySelector('.nav a[href="?view=delivery"]')?.remove();
    if (!permissions.history) document.querySelector('.nav a[href="?view=history"]')?.remove();
    if (!permissions.catalog) {
        document.querySelector('.nav a[href="?view=employees"]')?.remove();
        document.querySelector('.nav a[href="?view=products"]')?.remove();
    }
}
</script>
<script>
const dailyTips = [
    'Registra cada salida al momento de entregar.',
    'Confirma la cédula antes de guardar una salida.',
    'Verifica la sucursal donde se realiza la entrega.',
    'Usa las observaciones para dejar información importante.',
    'Consulta el historial para revisar las entregas anteriores.',
    'Mantén actualizados los nombres y códigos de los productos.'
];
const tipBox = document.querySelector('.tip');
if (tipBox) {
    let tipIndex = Math.floor(Date.now() / 60000) % dailyTips.length;
    const showTip = () => { tipBox.innerHTML = '<strong>Consejo del día</strong><br>' + dailyTips[tipIndex]; tipIndex = (tipIndex + 1) % dailyTips.length; };
    showTip();
    setInterval(showTip, 60000);
}
</script>
<div class="site-credit">Powered by <strong>andrews sierra</strong></div>
</body></html>
