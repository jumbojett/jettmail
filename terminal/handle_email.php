#!/usr/bin/php
<?php

/*
    Plugin Name: jettmail
    Plugin URI: http://mitre.org/
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

    // This script can only be called via the command line
    if (!(!empty($argc) && strstr($argv[0], basename(__FILE__)))) {
        die("This script can only be run from the command line");
    }

    // Ignore walled garden
    $_SERVER['REQUEST_URI'] = '/';

    // Load Elgg engine will not include plugins
    require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/engine/start.php");
    elgg_set_context('main');

    global $CONFIG;

    $Parser = new MimeMailParser();

    $stream = fopen("php://stdin", "r");
    //stream_set_timeout($stream, 4);
    $Parser->setStream($stream);

    $to = $Parser->getHeader('to');
    $from = $Parser->extractEmail('from');
    $subject = $Parser->getHeader('subject');

    if (!$from || $from == $to) {
        exit;
    }

    // Security checks begin
    $EmailVerify = new EmailAddressGenerator();
    $valid = $EmailVerify->verify($Parser->extractEmail('to'), $Parser->extractEmail('from'));

    if (!$valid) {
        trigger_error("jettmail caught an invalid incoming email. from :$from to: $to subject: $subject", E_USER_ERROR);
    } else {
        elgg_log("jettmail received a valid email. from :$from to: $to subject: $subject");
    }

    $text = $Parser->getMessageBody('text');
    $html = $Parser->getMessageBody('html');
    $attachments = $Parser->getAttachments();

    // Only extract plaintext portion for use
    $message_body = $text;

    // Remove the original body and just get the reply text
    // For outlook client replies
    list ($message_body) = explode("From: {$CONFIG->site->name} [mailto:", $message_body);
    // For outlook web client replies
    list ($message_body) = explode("________________________________", $message_body);

    // Eliminate a possible security hole where the special email address could be printed in the body
    $message_body = str_replace($Parser->extractEmail('to'), "", $message_body);

    // Elgg will auto-load html2text class from our classes plugin dir
    // Make sure this message body is free of HTML
    $h2t = new html2text($message_body);
    $message_body = $h2t->get_text();

    list($username, $domain) = explode('@', $Parser->extractEmail('to'));
    list($action_hash, $hash) = explode('+', $username);
    list($action, $type, $action_guid) = explode('.', $action_hash);

    list($user) = get_user_by_email($Parser->extractEmail('from'));

    $logged_in = login($user, false);

    // Generate new action tokens so elgg won't have a fit
    set_input('__elgg_token', generate_action_token(time()));
    set_input('__elgg_ts', time());

    if ($logged_in && is_numeric($action_guid) && $action && $type && $user) {

        elgg_trigger_plugin_hook("email:integration:$action" , $type,
            array('attachments' => $attachments
            , 'message' => $message_body
            , 'guid' => $action_guid
            , 'subject' => $subject));
    }

    logout();

