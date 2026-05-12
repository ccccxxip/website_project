<?php
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Здесь вы можете изменить логин и пароль на свои
    $valid_username = 'admin';
    $valid_password = 'admin123';
    
    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['admin'] = true;
        $_SESSION['username'] = $username;
        
        // Принудительный редирект
        header('Location: admin.php');
        exit;
    } else {
        $error = '❌ Неверный логин или пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ-панель</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #F4F4F2;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-card {
            background: white;
            border-radius: 32px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            border: 1px solid #EBE8E1;
        }
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo h1 {
            font-size: 2rem;
            font-weight: 600;
            color: #3A3A36;
        }
        .logo span {
            font-weight: 300;
            color: #8F8E89;
        }
        .logo p {
            font-size: 0.8rem;
            color: #7A7974;
            margin-top: 0.3rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #4A4943;
        }
        .form-group input {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 1px solid #E2DFD8;
            border-radius: 20px;
            font-size: 1rem;
            font-family: inherit;
            background: #FAFAF8;
            transition: 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #B9B2A2;
            background: white;
        }
        .btn-login {
            width: 100%;
            padding: 0.9rem;
            background: #3A3A36;
            color: white;
            border: none;
            border-radius: 40px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-login:hover {
            background: #2C2C2A;
        }
        .error {
            background: #FEF2F0;
            color: #D34C3A;
            padding: 0.8rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.85rem;
        }
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        .back-link a {
            color: #8B8982;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .back-link a:hover {
            color: #3A3A36;
        }
        .demo {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #EBE8E1;
            text-align: center;
            font-size: 0.75rem;
            color: #9A9892;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <h1>house<span>forms</span></h1>
            <p>вход в панель управления</p>
        </div>
        
        <?php if($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="username" placeholder="Введите логин" required autofocus>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" placeholder="Введите пароль" required>
            </div>
            <button type="submit" class="btn-login">🔐 Войти в админ-панель</button>
        </form>
        
        <div class="back-link">
            <a href="index.php">← Вернуться на сайт</a>
        </div>
        
        <div class="demo">
            <p>Демо-доступ: <strong>admin</strong> / <strong>admin123</strong></p>
            <p style="margin-top: 0.3rem;">⚠️ После загрузки на хостинг смените пароль в файле login.php</p>
        </div>
    </div>
</body>
</html>