<?php
  require("class.phpmailer.php");
  require("class.smtp.php");


$mail = new PHPMailer();
$mail->IsSMTP();
$mail->SMTPAuth = true;
$mail->Port = 465; 
$mail->SMTPSecure = 'ssl';
//$mail->IsHTML(true); 
$mail->CharSet = "utf-8";

// VALORES A MODIFICAR //
$mail->Host = 'c2150571.ferozo.com'; 
$mail->Username = 'webmaster@athicon.com'; 
$mail->Password = 'Catal1na/';

$mail->From = 'webmaster@athicon.com'; // Email desde donde envío el correo.
$mail->FromName = 'Contacto-Web';
$mail->AddAddress('administracion@athicon.com'); // Esta es la dirección a donde enviamos los datos del formulario

$mail->Subject = "Athicon.com - Contacto Web"; // Este es el titulo del email.
$Body = "";
$Body .= "Empresa: ";
$Body .= $_POST["empresa"];
$Body .= "\n";
$Body .= "Email: ";
$Body .= $_POST["email"];
$Body .= "\n";
$Body .= "Motivo: ";
$Body .= $_POST["motivo"];
$Body .= "\n";
$Body .= "Mensaje: ";
$Body .= $_POST["mensaje"];
$Body .= "\n";
$mensajeHtml = nl2br($Body);
$mail->Body = "{$mensajeHtml} <br /><br />{$name}<br />{$email}"; // Texto del email en formato HTML
$mail->AltBody = "{$Body} \n\n"; // Texto sin formato <HTML></HTML>

// echo $Body

//echo "OK"

// FIN - VALORES A MODIFICAR //

$success = $mail->Send(); 


// redirect to success page
if ($success && $errorMSG == ""){
   echo "OK";
}else{
    if($errorMSG == ""){
        echo "Ha ocurrido un error :(";
    } else {
        echo $errorMSG;
    }
}

?>
