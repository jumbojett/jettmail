<?php

global $CONFIG;
$notifications = $vars['notifications'];
$to_email = $vars['to_email'];
$subject = $vars['subject'];
list($user) = get_user_by_email($to_email);

/**
 * Thanks to Sean Powell for the email boilerplate
 * http://htmlemailboilerplate.com/
 */

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo $subject; ?></title>
    <style type="text/css">

            /***********
            Originally based on The MailChimp Reset from Fabio Carneiro, MailChimp User Experience Design
            More info and templates on Github: https://github.com/mailchimp/Email-Blueprints
            http://www.mailchimp.com &amp; http://www.fabio-carneiro.com

            INLINE: Yes.
            ***********/
            /* Client-specific Styles */
        #outlook a {padding:0;} /* Force Outlook to provide a "view in browser" menu link. */
        body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0; background-color: #F8F8F8}
            /* Prevent Webkit and Windows Mobile platforms from changing default font sizes, while not breaking desktop design. */
        .ExternalClass {width:100%;} /* Force Hotmail to display emails at full width */
        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;} /* Force Hotmail to display normal line spacing.  More on that: http://www.emailonacid.com/forum/viewthread/43/ */
        #backgroundTable {background-color: #F8F8F8;margin:0;padding:0;font-family: Calibri, Helvetica, Geneva, sans-serif; width:100% !important; line-height: 100% !important;}
            /* End reset */

            /* Some sensible defaults for images
            1. "-ms-interpolation-mode: bicubic" works to help ie properly resize images in IE. (if you are resizing them using the width and height attributes)
            2. "border:none" removes border when linking images.
            3. Updated the common Gmail/Hotmail image display fix: Gmail and Hotmail unwantedly adds in an extra space below images when using non IE browsers. You may not always want all of your images to be block elements. Apply the "image_fix" class to any image you need to fix.

            Bring inline: Yes.
            */
        img {outline:none; text-decoration:none; -ms-interpolation-mode: bicubic;}
        a img {border:none;}
        .image_fix {display:block;}

            /** Yahoo paragraph fix: removes the proper spacing or the paragraph (p) tag. To correct we set the top/bottom margin to 1em in the head of the document. Simple fix with little effect on other styling. NOTE: It is also common to use two breaks instead of the paragraph tag but I think this way is cleaner and more semantic. NOTE: This example recommends 1em. More info on setting web defaults: http://www.w3.org/TR/CSS21/sample.html or http://meiert.com/en/blog/20070922/user-agent-style-sheets/

            Bring inline: Yes.
            **/
        p {margin: 1em 0;}

            /** Hotmail header color reset: Hotmail replaces your header color styles with a green color on H2, H3, H4, H5, and H6 tags. In this example, the color is reset to black for a non-linked header, blue for a linked header, red for an active header (limited support), and purple for a visited header (limited support).  Replace with your choice of color. The !important is really what is overriding Hotmail's styling. Hotmail also sets the H1 and H2 tags to the same size.

            Bring inline: Yes.
            **/
        h1, h2, h3, h4, h5, h6 {color: black !important;}

        h1 a, h2 a, h3 a, h4 a, h5 a, h6 a {color: blue !important;}

        h1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active {
            color: red !important; /* Preferably not the same color as the normal header link color.  There is limited support for psuedo classes in email clients, this was added just for good measure. */
        }

        h1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited {
            color: purple !important; /* Preferably not the same color as the normal header link color. There is limited support for psuedo classes in email clients, this was added just for good measure. */
        }

            /** Outlook 07, 10 Padding issue: These "newer" versions of Outlook add some padding around table cells potentially throwing off your perfectly pixeled table.  The issue can cause added space and also throw off borders completely.  Use this fix in your header or inline to safely fix your table woes.

            More info: http://www.ianhoar.com/2008/04/29/outlook-2007-borders-and-1px-padding-on-table-cells/
            http://www.campaignmonitor.com/blog/post/3392/1px-borders-padding-on-table-cells-in-outlook-07/

            H/T @edmelly

            Bring inline: No.
            **/
        table td {border-collapse: collapse;}

            /** Remove spacing around Outlook 07, 10 tables

            More info : http://www.campaignmonitor.com/blog/post/3694/removing-spacing-from-around-tables-in-outlook-2007-and-2010/

            Bring inline: Yes
            **/
        table { border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; }

            /* Styling your links has become much simpler with the new Yahoo.  In fact, it falls in line with the main credo of styling in email, bring your styles inline.  Your link colors will be uniform across clients when brought inline.

            Bring inline: Yes. */
        /*a {color: orange;}*/

        a:visited { color: green; }

            /* Or to go the gold star route...
            a:link { color: orange; }
            a:visited { color: blue; }
            a:hover { color: green; }
            */

            /***************************************************
            ****************************************************
            MOBILE TARGETING

            Use @media queries with care.  You should not bring these styles inline -- so it's recommended to apply them AFTER you bring the other stlying inline.

            Note: test carefully with Yahoo.
            Note 2: Don't bring anything below this line inline.
            ****************************************************
            ***************************************************/

            /* NOTE: To properly use @media queries and play nice with yahoo mail, use attribute selectors in place of class, id declarations.
            table[class=classname]
            Read more: http://www.campaignmonitor.com/blog/post/3457/media-query-issues-in-yahoo-mail-mobile-email/
            */
        @media only screen and (max-device-width: 480px) {

            /* A nice and clean way to target phone numbers you want clickable and avoid a mobile phone from linking other numbers that look like, but are not phone numbers.  Use these two blocks of code to "unstyle" any numbers that may be linked.  The second block gives you a class to apply with a span tag to the numbers you would like linked and styled.

            Inspired by Campaign Monitor's article on using phone numbers in email: http://www.campaignmonitor.com/blog/post/3571/using-phone-numbers-in-html-email/.

            Step 1 (Step 2: line 224)
            */
            a[href^="tel"], a[href^="sms"] {
                text-decoration: none;
                color: black; /* or whatever your want */
                pointer-events: none;
                cursor: default;
            }

            .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
                text-decoration: default;
                color: orange !important; /* or whatever your want */
                pointer-events: auto;
                cursor: default;
            }
        }

            /* More Specific Targeting */

        @media only screen and (min-device-width: 768px) and (max-device-width: 1024px) {
            /* You guessed it, ipad (tablets, smaller screens, etc) */

            /* Step 1a: Repeating for the iPad */
            a[href^="tel"], a[href^="sms"] {
                text-decoration: none;
                color: blue; /* or whatever your want */
                pointer-events: none;
                cursor: default;
            }

            .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
                text-decoration: default;
                color: orange !important;
                pointer-events: auto;
                cursor: default;
            }
        }

        @media only screen and (-webkit-min-device-pixel-ratio: 2) {
            /* Put your iPhone 4g styles in here */
        }

            /* Following Android targeting from:
            http://developer.android.com/guide/webapps/targeting.html
            http://pugetworks.com/2011/04/css-media-queries-for-targeting-different-mobile-devices/  */
        @media only screen and (-webkit-device-pixel-ratio:.75){
            /* Put CSS for low density (ldpi) Android layouts in here */
        }
        @media only screen and (-webkit-device-pixel-ratio:1){
            /* Put CSS for medium density (mdpi) Android layouts in here */
        }
        @media only screen and (-webkit-device-pixel-ratio:1.5){
            /* Put CSS for high density (hdpi) Android layouts in here */
        }
            /* end Android targeting */
    </style>

    <!-- Targeting Windows Mobile -->
    <!--[if IEMobile 7]>
    <style type="text/css">

    </style>
    <![endif]-->

    <!-- ***********************************************
    ****************************************************
    END MOBILE TARGETING
    ****************************************************
    ************************************************ -->

    <!--[if gte mso 9]>
    <style>
            /* Target Outlook 2007 and 2010 */
    </style>
    <![endif]-->

</head>
<body>
<table cellpadding="0" cellspacing="0" border="0" id="backgroundTable"
       style="background-color: #F8F8F8;margin:0;padding:0;font:14px Calibri,sans-serif; width:100% !important; line-height: 100% !important;">
    <table id="banner" border="0" cellpadding="0" cellspacing="0"
           style="background-color: #F8F8F8;height: 70px;width:100%;padding:10px;border-bottom: 1px solid #E8E8E8; font:14px Calibri,sans-serif;">
        <tr>

            <td width="100%" nowrap align="left" style="padding-left: 10px">
                <span style="font-weight:bold"><?php echo $user->name; ?>,</span><br>
                <span style="font-size:smaller">Here's what's happening on <?php echo $CONFIG->sitename; ?></span>
            </td>
        </tr>
    </table>
    <table cellpadding="0" cellspacing="0" border="0" style="background-color: #FFFFFF;margin:0;padding:0;width:100%;border-bottom: 1px solid #E8E8E8;">
        <tr>
            <td>
                <table cellpadding="0" cellspacing="0" border="0" align="center">
                    <tbody>
                    <tr>
                        <td align="center" bgcolor="#ffffff">
                            <table style="margin:0 10px;" border="0" cellpadding="0" cellspacing="0" width="600">
                                <tbody>
                                <tr>
                                    <td height="20" width="600"></td>
                                </tr>

                                <tr>
                                    <td width="600">
                                        <table id="top-bar" bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0"
                                               width="600">
                                            <tbody>
                                            <tr>
                                                <td align="left" valign="middle">
                                                    <div style="color: #000; font-size: small; text-decoration: none;">
                                                        <webversion><a href="<?php echo $CONFIG->url; ?>activity">Web
                                                                version</a>
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

                                <tr>
                                    <td height="20" width="600"></td>
                                </tr>
                                <?php if (count($notifications) > 1) { ?>
                                    <tr>
                                        <td bgcolor="#ffffff" height="15" width="600"></td>
                                    </tr>

                                    <tr id="simple-content-row" style="text-align: left">
                                        <td bgcolor="#ffffff" width="600">
                                            <table border="1" cellpadding="0" cellspacing="0" width="600">
                                                <tbody>
                                                <tr>
                                                    <td width="30"></td>
                                                    <td width="540">
                                                        <repeater>

                                                            <layout label="Text only">
                                                                <table border="0" cellpadding="0" cellspacing="0"
                                                                       width="540">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td width="540">
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
                                                                        <td height="10" width="540"></td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </layout>


                                                        </repeater>
                                                    </td>

                                                </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <tr style="text-align: left">
                                    <td bgcolor="#ffffff" width="600">
                                        <table border="0" cellpadding="0" cellspacing="0" width="600">
                                            <tbody>
                                            <tr>
                                                <td width="600">
                                                    <repeater>

                                                        <layout label="Text only">
                                                            <table border="0" cellpadding="0" cellspacing="0"
                                                                   width="600">
                                                                <tbody>
                                                                <tr>
                                                                    <td width="600">
                                                                        <?php $anchor = 0; ?>
                                                                        <h2 style="border-bottom: 1px #C7C7C7 solid;">
                                                                            Details</h2>
                                                                        <?php foreach ($notifications as $guid => $entity) { ?>
                                                                            <?php foreach ($entity as $notification) { ?>
                                                                                <a name="<?= $anchor++ ?>"></a>
                                                                                <?= $notification->message ?>
                                                                                <hr style="color: #C7C7C7;background-color:#C7C7C7;height: 1px;border: 0;">
                                                                                <div
                                                                                    style="font-size: smaller; text-align: right; color: #C7C7C7;">
                                                                                    <?= date("l, F j, g:i A", $notification->time) ?>
                                                                                </div><br>
                                                                            <?php } ?>
                                                                        <?php } ?>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td height="10" width="600"></td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </layout>


                                                    </repeater>
                                                </td>

                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td width="600">
                                        <table id="footer" bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0"
                                               width="600">
                                            <tbody>
                                            <tr>
                                                <td valign="top" width="260">
                                                    <p align="left" style="font-size: small;">
                                                        You are receiving this because of your<br>
                                                        <a style="color: #000; font-weight: bold; text-decoration: none;"
                                                           href="<?php echo $CONFIG->url; ?>settings/user/">notification
                                                            preferences</a>
                                                    </p>

                                                </td>
                                                <td width="60"></td>
                                                <td valign="top" width="260">
                                                    <p align="right" style="font-size: small;">
                                                        Email replies are one time use per notification and expire <?=
                                                        date("M j, Y", strtotime('+'
                                                            . (int)elgg_get_plugin_setting("tokenDaysValid", "jettmail")
                                                            . ' days')) ?>
                                                    </p>
                                                </td>

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
            </td>
        </tr>

    </table>
</table>
</body>
</html>
