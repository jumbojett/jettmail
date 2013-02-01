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


class JettMailPlugin
{

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
        if (!elgg_get_plugin_setting('sig_key', 'jettmail')) {
            elgg_set_plugin_setting('sig_key', EmailAddressGenerator::generateSignatureKey(), 'jettmail');
        }

        /**
         * If the user has specifically requested that we regenerate the email signature key
         */
        if (elgg_get_plugin_setting("refreshSigKey", 'jettmail') == 'yes') {
            elgg_set_plugin_setting('sig_key', EmailAddressGenerator::generateSignatureKey(), 'jettmail');
            elgg_set_plugin_setting('refreshSigKey', null, 'jettmail');
        }

        /**
         * Make sure there is a default value for expiring tokens
         * If there isn't set it to 15 days
         */
        if (!(int)elgg_get_plugin_setting("tokenDaysValid", "jettmail")) {
            elgg_set_plugin_setting('tokenDaysValid', 15, "jettmail");
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

        global $CONFIG;

        elgg_set_context('jettmail_email_handler');

        //If the user has be disabled block all notifications sent to that user. ~Joe
        if ($to->inactive && $to->inactive == 'yes') {
            return true;
        }

        // fetch the plugin setting - an admin can disable email altogether
        $email_enabled = elgg_get_plugin_setting('enable_email', 'jettmail');
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


        // if the user has digest enabled
        if (isset($to->digest) && $to->digest == 'on'
            // then digest group discussion comments
            && elgg_hook_in_context('notify:annotation:message', 'group_topic_post')
            // digest new initial discussion posts
            && elgg_hook_in_context('notify:entity:message', 'object')
        ) {

            elgg_set_ignore_access(true);

            // elgg is horrible at handling metadata objects so we (un)serialize them
            $cached_notifications = unserialize($to->notifications);

            // initialize a notifications cache via metadata
            if (!is_array($cached_notifications)) {
                $cached_notifications = array();
            }
            if (!is_array($cached_notifications[$from->guid])) {
                $cached_notifications[$from->guid] = array();
            }

            // add the notification to the stack
            array_push($cached_notifications[$from->guid], (object)array(
                'subject' => $subject,
                'message' => $message
            ));

            $to->notifications = serialize($cached_notifications);

            $to->save();
            elgg_set_ignore_access(false);

        } else {
            return JettMail::sendMail($to->email, $subject, array(array((object)array('message' => $message))));
        }

        return true;

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
         * Handle incoming emails for discussion posting
         */
        elgg_register_plugin_hook_handler('email:integration:create', 'generic_comment',
            function ($hook_name, $entity_type, $return_value, $parameters) {

                // setup the parameters
                set_input('topic_guid', $parameters['guid']);
                set_input('entity_guid', $parameters['guid']);
                set_input('generic_comment', $parameters['message']);

                // set action
                set_input('action', 'comments/add');

                // perform the action
                action("comments/add");


            });

        /**
         * Handle incoming emails for updating status
         */
        elgg_register_plugin_hook_handler('email:integration:create', 'generic_comment',
            function ($hook_name, $entity_type, $return_value, $parameters) {

                // set update message
                // needs to be 140 characters or less
                set_input('body', substr($parameters['message'], 0, 140));
                set_input('method', 'site');

                // set action
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
                $method = $params['method'];
                $topic = $reply->getEntity();
                $poster = $reply->getOwnerEntity();
                $group = $topic->getContainerEntity();

                $email_text = elgg_view("jettmail/email/address/generate", array(
                    'action' => 'create.generic_comment',
                    'guid' => $topic->guid,
                    'to_email' => $params['to_entity']->email,
                    'text' => 'email a reply'
                ));

                // append the special email onto the message body
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

                // we append an email reply text if these are blog or page notifications
                if (in_array($subtype, array("blog", "page_top", "page"))) {

                    $email_text = elgg_view("jettmail/email/address/generate", array(
                        'action' => 'create.generic_comment',
                        'guid' => $entity->guid,
                        'to_email' => $params['to_entity']->email,
                        'text' => 'email a reply'
                    ));

                    // append the special email onto the message body
                    return $message . $email_text;
                }

                return NULL;

            }, 1000);

    }
}

$jett_mail = new JettMailPlugin();

