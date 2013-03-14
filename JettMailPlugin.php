<?php

/*
    Plugin Name: jettmail
    Plugin URI: http://id.mitre.org/
    Description: Extends elgg email capabilities
    Version: 2.0
    Author: Michael Jett
    Author URI: http://www.mitre.org/
    Author Email: mjett@mitre.org
    License:

      Copyright 2012 MITRE (mjett@mitre.org)

      This program is free software; you can redistribute it and/or modify
      it under the terms of the GNU General Public License, version 2, as
      published by the Free Software Foundation.

      This program is distributed in the hope that it will be useful,
      but WITHOUT ANY WARRANTY; without even the implied warranty of
      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
      GNU General Public License for more details.

      You should have received a copy of the GNU General Public License
      along with this program; if not, write to the Free Software
      Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

    Approved for Public Release: 12-2907. Distribution Unlimited
*/

class JettMailPlugin
{
    /**
     * @var array These are a set of default hooks we watch for to tell elgg whether or not to digest types of notifications
     */
    private $digestWatchHooks = array(
        // Digest group discussion comments
        array('hook' => 'notify:annotation:message', 'type' => 'group_topic_post'),
        array('hook' => 'action', 'type' => 'discussion/reply/save'),
        array('hook' => 'action', 'type' => 'comments/add'),
        // Digest new initial discussion posts
        array('hook' => 'notify:entity:message', 'type' => 'object'),
        array('hook' => 'action', 'type' => 'comments/add')
    );

    /**
     * Internal function to let us see if we can digest a hook in context
     */
    private function canDigest() {
        foreach ($this->digestWatchHooks as $watch) {
            if (elgg_hook_in_context($watch['hook'], $watch['type'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Public function that allows another plugin to add a digest watch hook
     * @param $hook
     * @param $type
     */
    public function addDigestWatchHook($hook, $type) {
        array_push($this->digestWatchHooks, array('hook' => $hook, 'type' => $type));
    }

    /**
     *
     */
    public function __construct() {

        // Initialize hooks
        elgg_register_event_handler('init', 'system', array($this, 'init'));
    }

    /**
     * Called when the elgg system initializes
     */
    public function init() {

        /**
         * Over-riding the email notification handler so we can use ours
         */
        register_notification_handler('email', array($this, 'emailHandler'));

        /**
         * Add a cron hook so we can send off daily digests and cleanup used tokens
         */
        elgg_register_plugin_hook_handler('cron', 'daily', array($this, 'cron'));

        /**
         * Over-ride permissions check for notifications
         */
        elgg_register_plugin_hook_handler('permissions_check', 'all', array($this, 'permissionsCheck'));

        /**
         * Extending the user settings so we can catch and save the digest option
         */
        elgg_register_plugin_hook_handler('usersettings:save', 'user', array($this, 'userSettings'));

        /**
         * Allow the user to turn on/off digest from notification form
         */
        elgg_extend_view("forms/account/settings", "jettmail/forms/account/settings/digest");

        /**
         * Generate a secret email signature key if one isn't present
         */
        if (!elgg_get_plugin_setting('sig_key', 'JettMail2')) {
            elgg_set_plugin_setting('sig_key', EmailAddressGenerator::generateSignatureKey(), 'JettMail2');
        }

        /**
         * If the user has specifically requested that we regenerate the email signature key
         */
        if (elgg_get_plugin_setting("refreshSigKey", 'JettMail2') == 'yes') {
            elgg_set_plugin_setting('sig_key', EmailAddressGenerator::generateSignatureKey(), 'JettMail2');
            elgg_set_plugin_setting('refreshSigKey', null, 'JettMail2');
        }

        /**
         * Make sure there is a default value for expiring tokens
         * If there isn't set it to 15 days
         */
        if (!(int)elgg_get_plugin_setting("tokenDaysValid", "JettMail2")) {
            elgg_set_plugin_setting('tokenDaysValid', 15, "JettMail2");
        }

        /**
         * Run database setup scripts once
         */
        run_function_once(function () {
            // we use a custom SQL table for determining used email integration keys
            // this is for optimal performance reasons
            run_sql_script(dirname(__FILE__) . '/schema/used_keys.sql');
        });

        /**
         * Register hooks for incoming emails
         * These are simple and closure based
         */
        self::handleIncomingEmails();

        /**
         * Register hooks for appending "email a reply" text to messages
         */
        self::appendEmailReplyToMessages();

    }

    /**
     * @param ElggEntity $from
     * @param ElggUser $to
     * @param $subject
     * @param $message
     * @param array $params
     * @return bool|string
     * @throws NotificationException
     */
    public function emailHandler(ElggEntity $from, ElggUser $to, $subject, $message, array $params = NULL) {

        // Handle email processing after elgg shuts down
        elgg_register_event_handler('shutdown', 'system', function () use ($from, $to, $subject, $message, $params) {
            global $CONFIG;

            elgg_set_context('jettmail_email_handler');

            // If the user has be disabled block all notifications sent to that user. ~Joe
            if ($to->inactive && $to->inactive == 'yes') {
                return true;
            }

            // Fetch the plugin setting - an admin can disable email altogether
            $email_enabled = elgg_get_plugin_setting('enable_email', 'JettMail2');
            if ($email_enabled == 'no') {
                return true;
            }

            if (!$from)
                throw new NotificationException(sprintf(elgg_echo('NotificationException:MissingParameter'), 'from'));

            if (!$to)
                throw new NotificationException(sprintf(elgg_echo('NotificationException:MissingParameter'), 'to'));

            if ($to->email == "")
                throw new NotificationException(sprintf(elgg_echo('NotificationException:NoEmailAddress'), $to->guid));

            if (!$params)
                $params = array();

            // Allow other plugins to hook in and modify the message
            $message = elgg_trigger_plugin_hook('notify:jettmail:message', $from->getSubtype(), array('to_entity' => $to), $message);

            // If the user has digest enabled
            // And the hook is in context
            if (isset($to->digest) && $to->digest == 'on' && self::canDigest()) {

                elgg_set_ignore_access(true);

                // Elgg is horrible at handling metadata objects so we (un)serialize them
                $cached_notifications = unserialize($to->notifications);

                // Initialize a notifications cache via metadata
                if (!is_array($cached_notifications)) {
                    $cached_notifications = array();
                }
                if (!is_array($cached_notifications[$from->guid])) {
                    $cached_notifications[$from->guid] = array();
                }

                // Add the notification to the stack
                array_push($cached_notifications[$from->guid], (object)array(
                    'subject' => $subject,
                    'message' => $message,
                    'time' => time()
                ));

                $to->notifications = serialize($cached_notifications);

                $to->save();
                elgg_set_ignore_access(false);

            } else {
                return JettMail::sendMail($to->email, $subject, array(array((object)array('message' => $message, 'time' => time()))));
            }

            return true;

        });


    }

    /**
     * @param $hook
     * @param $entity_type
     * @param $returnvalue
     * @param $params
     * @return string
     */
    public function cron($hook, $entity_type, $returnvalue, $params) {

        global $CONFIG;

        elgg_set_context('jettmail_cron');

        /**
         * First task: Let's send off some digests
         */

        $users = elgg_get_entities(array('type' => 'user', 'limit' => 0));
        foreach ($users as $user) {
            // if the user has some stored notifications send them out
            if ($user->notifications && count($user->notifications) != 0) {

                $notifications = unserialize($user->notifications);

                JettMail::sendMail($user->email, elgg_echo("jettmail:digest_subject"), $notifications);

                // reset notifications cache
                unset($user->notifications);
                $user->save();
            }
        }

        /**
         * Second task: Do some token cleanup from email integration
         */

        $query = "DELETE FROM {$CONFIG->dbprefix}jettmail_used_keys WHERE `expires` < NOW()";
        delete_data($query);


        return elgg_echo('jettmail:digest_cron_confirmation');
    }

    /**
     * @param $hook_name
     * @param $entity_type
     * @param $return_value
     * @param $parameters
     * @return bool|null
     */
    public function permissionsCheck($hook_name, $entity_type, $return_value, $parameters) {

        if (elgg_get_context() == 'jettmail_email_handler' || elgg_get_context() == 'jettmail_cron') {
            return true;
        }

        return null;
    }

    /**
     * @param $hook
     * @param $entity_type
     * @param $returnvalue
     * @param $params
     */
    public function userSettings($hook, $entity_type, $returnvalue, $params) {

        // Handle Digest
        $user = get_entity($_SESSION['user']->guid);

        if (($user) && ($user instanceof ElggUser)) {
            $user->digest = get_input('digest-input');
            $user->save();

        }
    }


    /**
     * Setups up hooks for handling incoming emails
     * Glues email to elgg actions
     */
    static public function handleIncomingEmails() {

        /**
         * Handle incoming emails for all generic comments (blogs, files, etc...)
         */
        elgg_register_plugin_hook_handler('email:integration:create', 'generic_comment',
            function ($hook_name, $entity_type, $return_value, $parameters) {

                // Setup the parameters
                set_input('topic_guid', $parameters['guid']);
                set_input('entity_guid', $parameters['guid']);
                set_input('generic_comment', $parameters['message']);

                // Set action
                set_input('action', 'comments/add');

                // Perform the action
                action("comments/add");


            });

        /**
         * Handle incoming emails for discussion posts
         */
        elgg_register_plugin_hook_handler('email:integration:create', 'group_topic_post',
            function ($hook_name, $entity_type, $return_value, $parameters) {

                set_input('entity_guid', $parameters['guid']);
                set_input('group_topic_post', $parameters['message']);

                // Set action
                set_input('action', 'discussion/reply/save');

                // Perform the action
                action("discussion/reply/save");


            });

        /**
         * Handle incoming emails for updating status
         */
        elgg_register_plugin_hook_handler('email:integration:create', 'generic_comment',
            function ($hook_name, $entity_type, $return_value, $parameters) {

                // Set update message
                // 140 characters or less
                set_input('body', substr($parameters['message'], 0, 140));
                set_input('method', 'site');

                // Set action
                set_input('action', 'thewire/add');

                // perform the action
                action("thewire/add");


            });

    }

    /**
     * Watch for certain message notifications and append "email a reply" text
     */
    static public function appendEmailReplyToMessages() {

        /**
         * Watch for new comments on discussions and append a reply to from email option
         * We needed to register this separate handler for new discussion posts since elgg doesn't trigger notify:entity:message
         */
        elgg_register_plugin_hook_handler('notify:annotation:message', 'group_topic_post',
            function ($hook, $type, $message, $params) {

                $reply = $params['annotation'];
                $topic = $reply->getEntity();

                $reply_action = 'create.generic_comment';

                // Group discussions get a different action since they do not truly utilize elgg's generic comment system
                if (get_input('action') == "groups/addpost"
                    || get_input('action') == "groups/addtopic"
                    || get_input('action') == "discussion/reply/save"
                    || get_input('action') == 'discussion/save'
                ) {
                    $reply_action = 'create.group_topic_post';
                }

                $email_text = elgg_view("jettmail/email/address/generate", array(
                    'action' => $reply_action,
                    'guid' => $topic->guid,
                    'to_email' => $params['to_entity']->email,
                    'text' => 'email a reply'
                ));

                // Append the special email onto the message body
                return $message . $email_text;

            }, 1000);

        /**
         * Watch for certain generic message notifications and append an email reply onto the message
         * This gets called last in the hook stack
         */
        elgg_register_plugin_hook_handler('notify:entity:message', 'object',
            function ($hook, $type, $message, $params) {

                global $CONFIG;

                $entity = $params['entity'];
                $to_entity = $params['to_entity'];
                $method = $params['method'];

                $subtype = $entity->getSubtype();

                // Append an email reply text if these are blog or page notifications
                if (in_array($subtype, array("blog", "page_top", "page"))) {

                    $email_text = elgg_view("jettmail/email/address/generate", array(
                        'action' => 'create.generic_comment',
                        'guid' => $entity->guid,
                        'to_email' => $params['to_entity']->email,
                        'text' => 'email a reply'
                    ));

                    // Append the special email onto the message body
                    return $message . $email_text;
                }

                return NULL;

            }, 1000);

    }
}

$jett_mail_plugin = new JettMailPlugin();

