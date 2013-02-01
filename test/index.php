<?php

/**
 * Simple file to test email integration
 */

// ignore walled garden
$_SERVER['REQUEST_URI'] = '/';

// Load Elgg engine will not include plugins
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/engine/start.php");
elgg_set_context('main');

global $CONFIG;

restore_error_handler();
// Report all PHP errors (see changelog)
error_reporting(E_ALL);


if (!elgg_is_logged_in()) {
    forward();
}

if (!elgg_is_admin_logged_in()) {
    die ("You must be an elgg admin to test email integration");
}

/**
 * First test the email address generator
 */
$test_email_address = 'no-reply@somedomain.com';
$test_action = md5(uniqid(rand(), TRUE));
$test_guid = 0;
$email_generator = new EmailAddressGenerator();
$reply_email = $email_generator->generateEmailAddress($test_action , $test_guid, $test_email_address);

$email_gen_test_result = '';
if ($email_generator->verify($reply_email,$test_email_address)) {
    $email_gen_test_result = 'Successfully passed';
} else {
    $email_gen_test_result = 'Failed';
}

// generate new action tokens so elgg won't have a fit
set_input('__elgg_token', generate_action_token(time()));
set_input('__elgg_ts', time());

$action_guid = get_input('action_guid');
$action = get_input('action');
$type = get_input('type');

if (is_numeric($action_guid) && $action && $type) {

    elgg_trigger_plugin_hook("email:integration:$action", $type,
        array('attachments' => null
        , 'message' => 'This is an email integration test'
        , 'guid' => $action_guid));

}

?>

<html>
<body>
<h2>Email generator test results</h2>
<p><?php echo $email_gen_test_result; ?> email generator test</p>
<h2>Post test</h2>
<p>This simulates an incoming email</p>
<form action="index.php">

    <label for="action_guid">Action guid:</label>
    <p>
    <input type="text" name="action_guid" id="action_guid"/>
    </p>

    <label for="type">Type:</label>

    <p>
    <select name="type" id="type">
        <option value="generic_comment">generic_comment</option>
    </select>
    </p>

    <label for="action">Action:</label>

    <p>
    <select name="action" id="action">
        <option value="create">create</option>
        <option value="update">update</option>
        <option value="delete">delete</option>
    </select>
    </p>
    <br/>
    <input type="submit"/>
</form>
</body>
</html>