<style>
    .table-form td {
        padding: 10px;
    }
</style>
<table border="0" class="table-form">
    <tr>
        <td>Enable email notifications?
            <i>Halt all email - without disabling the plugin</i>
        </td>
        <td>
            <?php $enable_email = $vars['entity']->enable_email; ?>
            <?php
            $params = array(
                'name' => 'params[enable_email]',
                'value' => $enable_email,
                'options' => array('yes', 'no'),
            );
            echo elgg_view('input/dropdown', $params);
            ?>
    </tr>
    <tr>
        <td>Expire tokens after one time use
            <i>If enabled tokens will expire after a one time use per-guid.</i>
        </td>
        <td>
            <?php $expire_tokens = $vars['entity']->expire_tokens; ?>
            <?php
            $params = array(
                'name' => 'params[expire_tokens]',
                'value' => $expire_tokens,
                'options' => array('no', 'yes'),
            );
            echo elgg_view('input/dropdown', $params);
            ?>
    </tr>
    <tr>
        <td>If you would like to refresh the signature key, then check the box.
            <i><strong>CAUTION!!</strong> DO NOT DO THIS
                unless the system is compromised!
                This will invalidate all previous email responses generated from email integration.</i>
        </td>
        <td>
            <?php  $refreshSigKey = $vars['entity']->refreshSigKey; ?>
            <input name="params[refreshSigKey]" value="1"
                   type="checkbox" <?php if ($refreshSigKey) echo "checked"; ?> />
        </td>
    </tr>
    <tr>
        <td>Hostname override (Enter the hostname. Leave blank for default.)</td>
        <td>    <?php
            $emailFromOverride = $vars['entity']->emailFromOverride;
            echo elgg_view('input/text', array(
                'name' => 'params[emailFromOverride]',
                'value' => $emailFromOverride,
                'class' => ' ',
            ));
            ?></td>
    </tr>
    <tr>
        <td>The number of days the user has to respond to an email</td>
        <td>    <?php
            $tokenDaysValid = $vars['entity']->tokenDaysValid;

            if (!$tokenDaysValid) {
                $tokenDaysValid = 15;
            }

            echo elgg_view('input/text', array(
                'name' => 'params[tokenDaysValid]',
                'value' => $tokenDaysValid,
                'class' => ' ',
            ));
            ?></td>
    </tr>

</table>



