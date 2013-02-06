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


/**
 *
 * @class JettMail
 *
 * Utilities for email
 *  - plaintext / html content types
 *  - mail process forking
 *  - encoding
 *
 */
class JettMail
{

    /**
     * @param  $string
     * @return string
     */
    static function toBase64($string) {
        return rtrim(chunk_split(
            base64_encode($string)));
    }

    /**
     * @static
     * @return object
     */
    private static function getHeader() {
        return (object)array(
            'html' => 'Content-type: text/html; charset=utf-8' . "\r\n"
                . 'Content-Transfer-Encoding: base64' . "\r\n",
            'plaintext' => 'Content-Type: text/plain; charset="utf-8"' . "\r\n"
                . 'Content-Transfer-Encoding: 7bit' . "\r\n",
            'multipart' => 'MIME-Version: 1.0' . "\r\n"
                . 'Content-type: multipart/alternative; '
        );
    }

    /**
     * @param $to_email
     * @param $subject
     * @param $notifications array
     * @return bool|string
     */
    static function sendMail($to_email, $subject, $notifications) {


        $message = elgg_view('jettmail/email/template', array('notifications' => $notifications, 'to_email' => $to_email));

        $html_body = self::toBase64($message);

        $h2t = new html2text($message);
        $plaintext_body = $h2t->get_text();

        // Generate a random boundary string
        $mime_boundary = '_x' . sha1((string)time()) . 'x';

        $headers = self::buildHeaders($to_email, $subject);
        $headers .= self::getHeader()->multipart;
        $headers .= sprintf('boundary="%s"', $mime_boundary) . "\r\n";

        $divider = sprintf('--%s', $mime_boundary) . "\r\n";
        $body = $divider
            . self::getHeader()->plaintext . "\r\n"
            . $plaintext_body . "\r\n"
            . $divider
            . self::getHeader()->html . "\r\n"
            . $html_body;

        // get the sendmail path from the php ini config settings
        $sendmail_path = trim(ini_get('sendmail_path'));

        /*if ($sendmail_path) {

            $email_message = escapeshellarg($headers . $body);
            $to_email = escapeshellarg($to_email);

            // send the email via sendmail process that gets spun off in the background
            // psuedo fork it so we don't have to wait
            return exec('nohup echo ' . $email_message . ' | 	' . $sendmail_path . ' ' . $to_email . ' > /dev/null 2> /dev/null & echo $!');

        } else {*/

            // if we can't find a sendmail path then just send email the old fashioned way
            return mail($to_email,
                $subject,
                $body,
                $headers);
        //}

    }


    /**
     * @param $to_email
     * @param $subject
     * @return string
     */
    static function buildHeaders($to_email, $subject) {

        global $CONFIG;

        $headers = "";

        $site = get_entity($CONFIG->site_guid);
        $from_email = elgg_trigger_plugin_hook('email:digest:from', 'none', array('$to' => $to_email, '$subject' => $subject));

        $from_email = ($from_email ? $from_email : self::extractFromEmail());

        $from_name = $site->name;

        $headers .= 'From: '
            . $from_name
            . ' <' . $from_email . '>' . "\r\n"
            . 'To: ' . $to_email . "\r\n"
            . 'Subject: ' . $subject . "\r\n";

        // add header to suppress Outlook auto responses
        $headers .= 'X-Auto-Response-Suppress: ' . 'OOF, DR, RN, NRN' . "\r\n";

        // add another header to suppress other email client auto responses
        $headers .= 'Precedence: ' . 'list' . "\r\n";

        return $headers;
    }


    /**
     * Determine the best from email address
     *
     * @return string
     * @author part functionality taken from elgg core
     */
    static function extractFromEmail() {
        global $CONFIG;

        $from_email = '';
        $site = get_entity($CONFIG->site_guid);
        // If there's an email address, use it - but only if its not from a user.
        if (($site) && (isset($site->email)))
            $from_email = $site->email;
        // If all else fails, use the domain of the site.
        else
            $from_email = 'noreply@' . get_site_domain($CONFIG->site_guid);

        return $from_email;
    }


}