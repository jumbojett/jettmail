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

/**
 * Requirements checking begin
 * The plugin will fail gracefully
 */
if ((float)phpversion() < 5.3) {
    register_error("Jett mail requires at least php 5.3 or greater");
} else {
    include_once('bootstrap.inc');
}

