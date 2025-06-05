<!DOCTYPE html>
<html>
<head>
    <title>Verificación de Suscripción</title>
</head>
<body>
    <h1>Verifica tu Suscripción</h1>
    <p>Hola,</p>
    <p>Has solicitado una suscripción al plan {{ $tier }} ({{ $paymentOption }}).</p>
    <p>Por favor, verifica tu correo haciendo clic en el siguiente enlace:</p>
    <a href="{{ url('/verify/' . $token) }}">Verificar Correo</a>
    <p>Si no solicitaste esto, ignora este correo.</p>
    <p>Gracias,</p>
    <p>Equipo de [Tu Juego]</p>
</body>
</html>