<?php

global $CONFIG;
$notifications = $vars['notifications'];
$to_email = $vars['to_email'];
list($user) = get_user_by_email($to_email);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head><title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body style="background-color: #F8F8F8;margin:0;padding:0;">
<table id="banner" border="0" cellpadding="0" cellspacing="0"
       style="background-color: #F8F8F8;height: 70px;width:100%;padding:10px;border-bottom: 1px solid #E8E8E8;">
    <tr>

        <td width="100%" nowrap align="left">
            <span style="font-weight:bold"><?php echo $user->name; ?>,</span><br>
            <span style="font-size:smaller">Here's what's happening on <?php echo $CONFIG->sitename; ?></span>
        </td>
    </tr>
</table>
<table id="background-table" border="0" cellpadding="0" cellspacing="0" width="100%"
       style="background-color: #fff;border-bottom: 1px solid #E8E8E8;">
    <tbody>
    <tr>
        <td align="center" bgcolor="#ffffff">
            <table style="margin:0 10px;" border="0" cellpadding="0" cellspacing="0" width="640">
                <tbody>
                <tr>
                    <td height="20" width="640"></td>
                </tr>

                <tr>
                    <td width="640">
                        <table id="top-bar" bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" width="640">
                            <tbody>
                            <tr>
                                <td width="30"></td>
                                <td align="left" valign="middle">
                                    <div style="color: #000; font-size: small; text-decoration: none;">
                                        <webversion><a href="<?php echo $CONFIG->url; ?>activity">Web version</a>
                                        </webversion>
                                        &nbsp;|&nbsp;
                                        <?= elgg_view("jettmail/email/address/generate", array(
                                        'action' => 'create.status_update',
                                        'guid' => 0,
                                        'to_email' => $to_email,
                                        'text' => 'Email a status update'
                                    )); ?>
                                        <br></div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <?php if (count($notifications) > 1) { ?>
                <tr>
                    <td bgcolor="#ffffff" height="15" width="640"></td>
                </tr>

                <tr id="simple-content-row">
                    <td bgcolor="#ffffff" width="640">
                        <table border="1" cellpadding="0" cellspacing="0" width="640">
                            <tbody>
                            <tr>
                                <td width="30"></td>
                                <td width="580">
                                    <repeater>

                                        <layout label="Text only">
                                            <table border="0" cellpadding="0" cellspacing="0" width="580">
                                                <tbody>
                                                <tr>
                                                    <td width="580">
                                                        <?php $anchor = 0; ?>
                                                        <h2>Summary</h2>
                                                        <?php foreach ($notifications as $guid => $entity) { ?>
                                                        <h3 label="Title"><?= get_entity($guid)->name; ?></h3>

                                                        <ul>
                                                            <?php foreach ($entity as $notification) { ?>
                                                            <li>
                                                                <a href="#<?= $anchor++; ?>"><?= $notification->subject; ?></a>
                                                            </li>
                                                            <?php } ?>
                                                        </ul>

                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td height="10" width="580"></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </layout>


                                    </repeater>
                                </td>
                                <td width="30"></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                    <?php } ?>

                <tr>
                    <td bgcolor="#ffffff" width="640">
                        <table border="0" cellpadding="0" cellspacing="0" width="640">
                            <tbody>
                            <tr>
                                <td width="30"></td>
                                <td width="580">
                                    <repeater>

                                        <layout label="Text only">
                                            <table border="0" cellpadding="0" cellspacing="0" width="580">
                                                <tbody>
                                                <tr>
                                                    <td width="580">
                                                        <?php $anchor = 0; ?>
                                                        <h2 style="border-bottom: 1px #C7C7C7 solid;">Details</h2>
                                                        <?php foreach ($notifications as $guid => $entity) { ?>
                                                        <?php foreach ($entity as $notification) { ?>
                                                            <a name="<?= $anchor++ ?>"></a>
                                                            <?= $notification->message ?>
                                                            <hr style="color: #C7C7C7;background-color:#C7C7C7;height: 1px;border: 0;">
                                                            <div style="font-size: smaller; text-align: right; color: #C7C7C7;">
                                                                <?= date("l, F j, g:i A" , $notification->time) ?>
                                                            </div><br>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td height="10" width="580"></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </layout>


                                    </repeater>
                                </td>
                                <td width="30"></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>

                <tr style="font-size: small;">
                    <td width="640">
                        <table id="footer" bgcolor="#fff" border="0" cellpadding="0" cellspacing="0" width="640">
                            <tbody>
                            <tr>
                                <td width="30"></td>
                                <td valign="top" width="260">
                                    <p align="left">
                                        You are receiving this because of your<br>
                                        <a style="color: #000; font-weight: bold; text-decoration: none;"
                                           href="<?php echo $CONFIG->url; ?>settings/user/">notification
                                            preferences</a>
                                    </p>

                                </td>
                                <td width="60"></td>
                                <td valign="top" width="260">
                                    <p align="right">
                                        Email replies are one time use per notification and expire <?=
                                        date("M j, Y", strtotime('+'
                                            . (int)elgg_get_plugin_setting("tokenDaysValid", "jettmail")
                                            . ' days')) ?>
                                    </p>
                                </td>
                                <td width="30"></td>
                            </tr>

                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
