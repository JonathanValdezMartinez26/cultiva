<?php
$anio = date('Y');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Cultiva</title>
    <link rel="shortcut icon" href="/img/logo.png">

    <?= $header; ?>
</head>

<body style="height: 100vh; width: 100vw">
    <div style="height: 100%; width: 100%; display: flex; align-items: center; justify-content: center; padding: 0; margin: 0">
        <section class="login_content" style="padding: 0; width: 20%;">
            <div style="text-align: center; height:100px">
                <img src="/img/logo.png" alt="Login" width="350">
            </div>
            <form id="login" action="/Login/crearSession" method="POST" class="form-horizontal" name="login">
                <h1 style="color: #C43136; font-size: 30px; text-align: center;">Iniciar Sesión</h1>
                <div class="col-md-1 col-sm-1 col-xs-1" style="height:15px" id="availability"></div>
                <div class="col-md-12 col-sm-12 col-xs-12" style="height:80px">
                    <input type="text" name="usuario" id="usuario" class="form-control col-md-6 col-xs-12" placeholder="Usuario" onkeyup="mayusculas(event)" required>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <input type="password" name="password" id="password" class="form-control col-md-5 col-xs-12" placeholder="Contraseña" onkeypress="enviar_formulario(event)" required>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <button type="button" id="btnEntrar" class="btn btn-warning col-md-4 col-sm-4 col-xs-4 pull-right" style="background: #C43136; border-color: #C43136">Entrar <i class="glyphicon glyphicon-log-in"></i></button>
                </div>
                <div class="clearfix"></div>
                <div class="separator">
                    <br>
                    <div>
                        <p>© <?= $anio ?> - Al utilizar los servicios de Financiera Cultiva, los usuarios están de acuerdo con las políticas de privacidad y términos de uso establecidos por la empresa.</p>
                    </div>
                </div>
            </form>
        </section>
    </div>
    <?= $footer; ?>
</body>

</html>