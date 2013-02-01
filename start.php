<?php

/**
 *
 * @package jettmail
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Michael Jett
 * @copyright MITRE
 * @link http://mitre.org/
 *
 * Approved for Public Release: 12-2907. Distribution Unlimited
 *
 **/

global $CONFIG;
ini_set('short_open_tag',1);

/**
 * Requirements checking begin
 * The plugin will fail gracefully
 */
if ((float)phpversion() < 5.3) {
    register_error("Jett mail requires at least php 5.3 or greater");
} else {
    include_once('JettMailPlugin.php');
}

/**
 * This pushes the hooks currently being used to a global context stack
 *
 * We were going to use the elgg global context functions as part of this, but a bunch of the Elgg core
 * menu stuff mucks up the global space. To make this consistent we tacked on our own $CONFIG->hook_context
 *
 * This is useful because it gives our plugin context awareness
 */
elgg_register_plugin_hook_handler('all', 'all',
    function ($hook, $type, $message, $params) {

        global $CONFIG;

        if (!$CONFIG->hook_context) {
            $CONFIG->hook_context = array();
        }

        // make sure we don't have duplicate hooks in the stack
        if (!elgg_hook_in_context($hook, $type))
            array_push($CONFIG->hook_context, (
            (object)array(
                'hook' => $hook,
                'type' => $type
            )
            ));
    });

/**
 * Ah the missing elgg link
 * This allows us to see if the elgg hooks are in context
 *
 * @param $hook
 * @param $type
 *
 * @return bool
 */
function elgg_hook_in_context($hook, $type) {

    global $CONFIG;

    return in_array((
    (object)array(
        'hook' => $hook,
        'type' => $type
    )
    ), $CONFIG->hook_context);

}

