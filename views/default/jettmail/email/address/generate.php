<?php
$email_generator = new EmailAddressGenerator();
$reply_email = $email_generator->generateEmailAddress($vars['action'] , $vars['guid'], $vars['to_email']);
?>

<a href="mailto:<?= $reply_email ?>?subject=RE:<?= rawurlencode($vars['subject']) ?>">
<?= $vars['text']; ?>
</a>
