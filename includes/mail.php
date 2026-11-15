<?php
  // Envío de mails por SMTP (Gmail + contraseña de aplicación) con PHPMailer.
  // InfinityFree no deja usar mail(), por eso se manda por SMTP.
  // Datos en el .env: SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_FROM_NAME.
  require_once __DIR__ . '/../config/env.php';
  require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
  require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
  require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception as PHPMailerException;

  // Devuelve true si el mail salió. Si falla, deja el motivo en el log y devuelve false.
  function enviar_mail($para, $nombre, $asunto, $html, $texto) {
    $usuario = env('SMTP_USER');
    $clave   = str_replace(' ', '', env('SMTP_PASS'));
    $puerto  = (int) env('SMTP_PORT', '587');

    if ($usuario === '' || $clave === '') {
      error_log('enviar_mail: faltan SMTP_USER o SMTP_PASS en el .env');
      return false;
    }

    $mail = new PHPMailer(true);

    try {
      $mail->isSMTP();
      $mail->Host       = env('SMTP_HOST', 'smtp.gmail.com');
      $mail->Port       = $puerto;
      $mail->SMTPAuth   = true;
      $mail->Username   = $usuario;
      $mail->Password   = $clave;
      $mail->SMTPSecure = $puerto === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Timeout    = 15;
      $mail->CharSet    = 'UTF-8';

      // Gmail solo deja mandar desde la misma cuenta que se autentica
      $mail->setFrom($usuario, env('SMTP_FROM_NAME', 'DKBZA Barber Studio'));
      $mail->addAddress($para, $nombre);

      $mail->isHTML(true);
      $mail->Subject = $asunto;
      $mail->Body    = $html;
      $mail->AltBody = $texto;

      $mail->send();
      return true;
    } catch (PHPMailerException $ex) {
      error_log('enviar_mail: ' . $mail->ErrorInfo);
      return false;
    }
  }
?>