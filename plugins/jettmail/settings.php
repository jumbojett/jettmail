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
<br />(this setting allows you to halt all email - without disabling the plugin)<br /><br />

<?php
$whitelist = $vars['entity']->whitelist;


echo 'White List (if no White List is defined email will send normally)';
echo elgg_view('input/text', array('name' => 'params[whitelist]',
				   'value' => $whitelist));


?>

