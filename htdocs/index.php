<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stefblioteca – Acasă</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f8;
            color: #333;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }

        header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 2.2em;
        }

        .user-info {
            background: #eef2f3;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .role-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
            background: #3498db;
            color: white;
        }

        .menu-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .menu-list li {
            margin-bottom: 12px;
        }

        .menu-list a {
            display: block;
            padding: 12px;
            background: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
            font-weight: 500;
        }

        .menu-list a:hover {
            background: #34495e;
        }

        /* Culori specifice pentru butoane */
        .btn-special { background: #8e44ad !important; } /* Admin */
        .btn-special:hover { background: #7d3c98 !important; }

        .btn-bibliotecar { background: #f39c12 !important; }
        .btn-bibliotecar:hover { background: #d68910 !important; }

        .btn-logout {
            display: inline-block;
            margin-top: 15px;
            color: #e74c3c;
            text-decoration: none;
            font-weight: bold;
        }

        .auth-links a {
            display: inline-block;
            margin: 10px;
            padding: 10px 25px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>📚 Stefblioteca</h1>
        <p style="color: #7f8c8d;">Sistem de Gestiune Bibliotecă</p>
    </header>

    <?php if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != ""): ?>
        
        <?php 
            $rol = $_SESSION["user_rol"] ?? 'cititor'; 
            $nume = $_SESSION["user_name"] ?? 'Utilizator';
        ?>

        <div class="user-info">
            <p>Bun venit, <strong><?php echo htmlspecialchars($nume); ?></strong>!</p>
            <span class="role-badge"><?php echo htmlspecialchars($rol); ?></span>
        </div>

        <ul class="menu-list">
            <li><a href="carti/carti_list.php">🔍 Vizualizare Cărți</a></li>

            <?php if ($rol === 'cititor'): ?>
                <li><a href="carti/rezervarile_mele.php">📖 Rezervările Mele</a></li>
            <?php endif; ?>

            <?php if ($rol === 'bibliotecar' || $rol === 'admin'): ?>
                <li><a href="carti/gestiune_rezervari.php" class="btn-bibliotecar">📋 Gestiune Împrumuturi</a></li>
            <?php endif; ?>

            <?php if ($rol === 'admin'): ?>
                <li><a href="carti/admin_utilizatori.php" class="btn-special">👥 Administrare Utilizatori</a></li>
            <?php endif; ?>
        </ul>

        <a href="logout.php" class="btn-logout">Ieșire cont (Logout)</a>

    <?php else: ?>

        <div class="auth-links">
            <p>Pentru a accesa resursele bibliotecii, te rugăm să te autentifici.</p>
            <a href="login.php">Login</a>
            <a href="register.php" style="background: #2ecc71;">Register</a>
        </div>

    <?php endif; ?>
</div>

</body>
</html>