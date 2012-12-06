<?php
/**
 * Mail settings - allowing admins to cut off email when needed
 */
?>
Enable email notifications?
<?php
$params = array(
    'name' => 'params[enable_email]',
    'value' => $vars['entity']->enable_email,
    'options' => array('yes', 'no'),
);
echo elgg_view('input/dropdown', $params);
?>
<br/>(this setting allows you to halt all email - without disabling the plugin)<br/><br/>

<?php
$whitelist = $vars['entity']->whitelist;


echo 'White List (if no White List is defined email will send normally)';
echo elgg_view('input/text', array('name' => 'params[whitelist]',
    'value' => $whitelist));


?>

<p>
    <?php

    echo "If you would like to refresh the signature key, then select yes from the drop down. CAUTION!! DO NOT DO THIS unless the system is compromised!
    This will invalidate all previous email responses generated from email integration.: <br/> ";

    $refreshSigKey = $vars['entity']->refreshSigKey;

    ?>

    <select name="params[refreshSigKey]">
        <option value="" <?php if (!$refreshSigKey) echo " selected=\"yes\" "; ?>></option>
        <option value="yes" <?php if ($refreshSigKey == 'yes') echo " selected=\"yes\" "; ?>>Yes, I understand that this
            is something I probably should not do.
        </option>
    </select>

    <?php
    $emailFromOverride = $vars['entity']->emailFromOverride;
    echo "\n<br/>Hostname override (Enter the hostname. Leave blank for default.):<br/> ";
    echo elgg_view('input/text', array(
        'name' => 'params[emailFromOverride]',
        'value' => $emailFromOverride,
        'class' => ' ',
    ));
    ?>

    <?php
    $tokenDaysValid = $vars['entity']->tokenDaysValid;

    if (!$tokenDaysValid) {
        $tokenDaysValid = 15;
    }

    echo "\n<br/>Number of days the user has to respond to an email:<br/> ";
    echo elgg_view('input/text', array(
        'name' => 'params[tokenDaysValid]',
        'value' => $tokenDaysValid,
        'class' => ' ',
    ));
    ?>


</p>
