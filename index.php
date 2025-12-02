<?php
session_start();
$csvFile = 'usuarios.csv'; 
$error = '';
$mensaje = '';

function cargarUsuarios($archivo) {
    $usuarios = [];
    if (file_exists($archivo) && ($handle = fopen($archivo, "r")) !== FALSE) {
        if (fgetcsv($handle) === false && !feof($handle)) {
        } elseif (feof($handle)) {
            
        }

        while (($data = fgetcsv($handle)) !== FALSE) {
            // Formato: [0]=>usuario, [1]=>contrasena
            if (count($data) >= 2) {
                $usuarios[$data[0]] = $data[1];
            }
        }
        fclose($handle);
    }
    return $usuarios;
}

function verificarCredenciales($usuario, $contrasena, $usuarios) {
    return isset($usuarios[$usuario]) && $usuarios[$usuario] === $contrasena;
}

function agregarUsuario($usuario, $contrasena, $archivo) {
    if (($handle = fopen($archivo, "a")) !== FALSE) {
        if (filesize($archivo) === 0) {
              fputcsv($handle, ['usuario', 'contrasena']);
        }
        fputcsv($handle, [$usuario, $contrasena]);
        fclose($handle);
        return true;
    }
    return false;
}


if (isset($_POST['logout'])) {
    session_destroy();
    // Redirigir a index.php (el nombre del archivo actual)
    header('Location: ' . basename($_SERVER['PHP_SELF']));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar'])) {
    $nuevoUsuario = trim($_POST['nuevo_usuario']);
    $nuevaContrasena = trim($_POST['nueva_contrasena']);
    $usuarios = cargarUsuarios($csvFile);

    if (empty($nuevoUsuario) || empty($nuevaContrasena)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (isset($usuarios[$nuevoUsuario])) {
        $error = "El usuario '{$nuevoUsuario}' ya existe.";
    } else {
        if (agregarUsuario($nuevoUsuario, $nuevaContrasena, $csvFile)) {
            $mensaje = "Usuario agregado con éxito! Ahora puedes iniciar sesión.";
            $vista = 'login'; 
        } else {
            $error = "Error al guardar el usuario en el archivo CSV.";
        }
    }
    if (isset($vista) && $vista == 'login') {
          unset($nuevoUsuario);
          unset($nuevaContrasena);
    } else {
        $vista = 'registro';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $usuario = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);
    $usuarios = cargarUsuarios($csvFile);

    if (verificarCredenciales($usuario, $contrasena, $usuarios)) {
        $_SESSION['usuario'] = $usuario;
        header('Location: ' . basename($_SERVER['PHP_SELF']));
        exit;
    } else {
        $error = "Usuario o Contraseña incorrectos.";
    }
}



$vista = 'login'; 

if (isset($_SESSION['usuario'])) {
    $vista = 'exito';
    $nombreUsuario = htmlspecialchars($_SESSION['usuario']);
} elseif (isset($_POST['show_registro']) || (isset($_POST['registrar']) && $error) || (isset($vista) && $vista == 'registro')) {
    $vista = 'registro';
}

if ($vista !== 'registro' || !isset($error)) {
    $nuevoUsuario = '';
    $nuevaContrasena = '';
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #F5F5DC;
            color:rgba; 
            margin: 0;
            padding: 0;
        }
        .contenedor { 
            width: 90%;
            max-width: 350px; 
            margin: 50px auto; 
            padding: 20px; 
            border: 1px solid #5c0e0b; 
            border-radius: 8px; 
            background-color: #F5F5DC; 
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        }
        h2 { 
            text-align: center; 
            color: rgba; 
            border-bottom: 2px solid #5c0e0b;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .error { 
            color: #ffdddd; 
            background-color: #5c0e0b; 
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .exito { 
            color: #d4edda; 
            background-color: #28542c; 
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #5c0e0b;
            border-radius: 4px;
            box-sizing: border-box; 
            background-color: #c44a46;
            color: #f7f5f5;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #5c0e0b; 
            outline: none;
        }

        
        button {
            background-color: #5c0e0b; 
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-bottom: 10px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #8a1310;
        }
        button[name="show_registro"], button[name="show_login"] {
            background-color: #5c0e0b;
            color: #f7f5f5;
        }
        button[name="show_registro"]:hover, button[name="show_login"]:hover {
            background-color: #5c5c5c;
        }
        hr {
            border: 0;
            height: 1px;
            background-color: #5c0e0b;
            margin: 20px 0;
        }
        .contenedor form[method="POST"] button[name="logout"] {
            background-color: #960601; 
; 
        }
        .contenedor form[method="POST"] button[name="logout"]:hover {
            background-color: #eb864b; 
;
        }

        .anuncio {
            background-color: #eb864b; 
;
            border: 2px solid #fcfbfb;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
        }

        .anuncio h3 {
            color: #ffcc00;
            margin-top: 0;
        }

        .anuncio p {
            font-size: 0.9em;
            color: #5c0e0b;
        }
    </style>
</head>
<body>
    <div class="contenedor">

    <?php if ($vista === 'exito'): ?>

        <h2>¡Bienvenido, <?php echo $nombreUsuario; ?>!</h2>
        <p>Has iniciado sesión correctamente.</p>
        
        <hr>

        <div class="anuncio">
            <h3>Descubre la Guerra Franco-Prusiana (1870-1871) </h3>
            <p>Sumérgete en el conflicto que redefinió el mapa de Europa y condujo a la Unificación Alemana y la Tercera República Francesa.</p>
            <p>**Batallas, Estrategias y el Sitio de París.**</p>
            <form action="index1.html" method="GET">
                 <button type="submit">CONTINUAR a la Web</button>
            </form>
        </div>
        <hr>

        <form method="POST" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">
            <button type="submit" name="logout">Cerrar Sesión</button>
        </form>

    <?php elseif ($vista === 'registro'):  ?>

        <h2>Agregar Nuevo Usuario</h2>

        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <?php if ($mensaje): ?><p class="exito"><?php echo $mensaje; ?></p><?php endif; ?>

        <form method="POST" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="registrar" value="1">
            
            <label for="nuevo_usuario">Nuevo Usuario:</label>
            <input type="text" id="nuevo_usuario" name="nuevo_usuario" required value="<?php echo htmlspecialchars($nuevoUsuario ?? ''); ?>"><br>
            
            <label for="nueva_contrasena">Contraseña:</label>
            <input type="password" id="nueva_contrasena" name="nueva_contrasena" required><br>
            
            <button type="submit">Registrar</button>
        </form>
        
        <hr>
        <form method="POST" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">
            <button type="submit" name="show_login">Volver a Login</button>
        </form>

    <?php else:  ?>

        <h2>Iniciar Sesión</h2>

        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <?php if ($mensaje): ?><p class="exito"><?php echo $mensaje; ?></p><?php endif; ?>

        <form method="POST" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="login" value="1">
            
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" required><br>
            
            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required><br>
            
            <button type="submit">Entrar</button>
        </form>
        
        <hr>
        <form method="POST" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">
            <button type="submit" name="show_registro">¿No tienes cuenta? Regístrate</button>
        </form>

    <?php endif; ?>

    </div>
</body>
</html>