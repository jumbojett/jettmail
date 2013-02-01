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


/**
 * @class EmailAddressGenerator
 * Signs and creates email addresses for reply to emails
 */
class EmailAddressGenerator {

    /**
     * @param  $action
     * @param  $toEmail
     * @param  $fromDomain
     * @param  $secret
     * @param  $date string
     * @return string
     */
    public function sign($action, $toEmail, $fromDomain, $secret, $date) {

        $sign_this = "$action $toEmail $fromDomain $date";

        return hash_hmac('sha256', $sign_this, $secret);

    }

    /**
     * Generates an email address
     * @param $action
     * @param $guid
     * @param $toEmail
     * @return string
     */
    public function generateEmailAddress($action, $guid, $toEmail) {

        // this is the full actions string including the guid on where the action is to be performed
        $full_action = $action . '.' . $guid;

        // ex: create.comment.1234+b8e7ae12510bdfb1812e463a7f086122cf37e4f7@handshake.mitre.org
        // return a special email address with the signature token this is signed with the current date string
        return $full_action
            . '+'
            . $this->sign($full_action, $toEmail, $this->getHostName(), elgg_get_plugin_setting('sig_key', 'jettmail'), date("Y-m-d"))
            . '@'
            . $this->getHostName();
    }

    /**
     * @return string
     */
    private function getHostName () {

        global $CONFIG;
        // if the admin has set an alternative hostname for us to use
        $email_from = trim(elgg_get_plugin_setting("emailFromOverride", "jettmail"));

        if (!$email_from) {
            $email_from = get_site_domain($CONFIG->site_guid);
        }

        return $email_from;
    }

    /**
     * @param $emailAddress
     * @param $from
     * @return bool
     */
    public function verify($emailAddress, $from) {

        global $CONFIG;

        $secret = elgg_get_plugin_setting('sig_key', 'jettmail');

        list ($userName, $domain) = explode('@', $emailAddress);
        list ($action, $hash) = explode('+', $userName);

        $days_valid = (int) elgg_get_plugin_setting("tokenDaysValid", "jettmail");

        for ($days_ago = 0; $days_ago <= $days_valid; ++$days_ago) {
            if ($hash == $this->sign($action, $from, $domain, $secret, date("Y-m-d", strtotime($days_ago . " days ago")))) {

                // insert a key into the database if it fails then we know that the key has already been used
                $query = "INSERT INTO {$CONFIG->dbprefix}jettmail_used_keys (`key` ,`expires`) VALUES ('{$hash}', DATE_ADD(NOW(), INTERVAL {$days_valid} DAY))";

                return (insert_data($query) !== FALSE);
            }

        }

        return false;
    }

    /**
     * Generates a random key for email verification
     * @param int $length
     * @return string
     */
    static function generateSignatureKey($length = 16) {

        $pr_bits = '';

        // Unix/Linux platform?
        $fp = @fopen('/dev/urandom', 'rb');
        if ($fp !== FALSE) {
            $pr_bits .= @fread($fp, $length);
            @fclose($fp);
        }

        // MS-Windows platform?
        if (@class_exists('COM')) {
            // http://msdn.microsoft.com/en-us/library/aa388176(VS.85).aspx
            try {
                $CAPI_Util = new COM('CAPICOM.Utilities.1');
                $pr_bits .= $CAPI_Util->GetRandom($length, 0);

                // if we ask for binary data PHP munges it, so we
                // request base64 return value.  We squeeze out the
                // redundancy and useless ==CRLF by hashing...
                if ($pr_bits) {
                    $pr_bits = md5($pr_bits, TRUE);
                }
            } catch (Exception $ex) {
                // echo 'Exception: ' . $ex->getMessage();
            }
        }

        if (strlen($pr_bits) < $length) {
            // do something to warn system owner that
            // pseudorandom generator is missing
            die("pseudorandom generator is missing in the email integration plugin");
        }

        return sha1($pr_bits);
    }

}
