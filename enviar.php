<?php
// Mycelium Stage · envío del formulario de contacto a info@myceliumstage.com
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function responder($ok, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode(array('ok' => $ok));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { responder(false, 405); }

// Campo trampa para robots: si viene relleno, fingimos éxito y no enviamos nada
if (!empty($_POST['_honey'])) { responder(true); }

function limpio($v, $max) {
    $v = isset($_POST[$v]) ? trim((string)$_POST[$v]) : '';
    $v = str_replace(array("\r", "\n", "%0a", "%0d"), ' ', $v);   // evita inyección de cabeceras
    return mb_substr($v, 0, $max, 'UTF-8');
}

$nombre   = limpio('Nombre', 120);
$email    = limpio('email', 160);
$telefono = limpio('Teléfono', 40);
$tipo     = limpio('Tipo de evento', 80);
$mensaje  = isset($_POST['Mensaje']) ? trim((string)$_POST['Mensaje']) : '';
$mensaje  = mb_substr($mensaje, 0, 5000, 'UTF-8');

if ($nombre === '' || $mensaje === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { responder(false, 422); }

$para    = 'info@myceliumstage.com';
$asunto  = '=?UTF-8?B?' . base64_encode('Nuevo mensaje desde la web de Mycelium Stage') . '?=';
$cuerpo  = "Nombre: $nombre\n"
         . "Correo: $email\n"
         . "Teléfono: " . ($telefono !== '' ? $telefono : '-') . "\n"
         . "Tipo de evento: $tipo\n\n"
         . "Mensaje:\n$mensaje\n";
$cab  = "From: Web Mycelium Stage <info@myceliumstage.com>\r\n";
$cab .= "Reply-To: =?UTF-8?B?" . base64_encode($nombre) . "?= <$email>\r\n";
$cab .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n";

responder(@mail($para, $asunto, $cuerpo, $cab) ? true : false, 200);
