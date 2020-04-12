<?php
require '../vendor/autoload.php';
use App\CsrfMiddleware;

$session = [];
$csrfMiddleware =  new CsrfMiddleware($session, 200);
?>

<h1> Contact form :</h1>
<form action="/contact" method="post" style="width: 260px;">
    <div style="margin-bottom: 10px;">
        <label for="name" style="margin-right: 30px">Nom :</label>
        <input type="text" id="name" name="user_name">
    </div>
    <div style="margin-bottom: 10px;">
        <label for="mail" style="margin-right: 25px">e-mail :</label>
        <input type="email" id="mail" name="user_mail">
    </div>
    <div style="margin-bottom: 10px;">
        <label for="msg" style="margin-right: 15px">Message :</label>
        <textarea id="msg" name="user_message"></textarea>
    </div>

    <input type="hidden" name="_csrf" value="<?php try {
        echo $csrfMiddleware->generateToken();
    } catch (Exception $e) {
    } ?>">

    <div style="margin-bottom: 10px; text-align: right">
        <input type="submit" value="Envoyer">
    </div>

</form>