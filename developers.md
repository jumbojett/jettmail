Developer's Guide
-----------------

## Incoming Email Hooks

 - email:integration:create
 - email:integration:update
 - email:integration:delete

### Example

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

##Email address generation
Use the jettmail address generator view

### Example
        $email_text = elgg_view("jettmail/email/address/generate", array(
            'action' => 'create.generic_comment',
            'guid' => $topic->guid,
            'to_email' => $params['to_entity']->email,
            'text' => 'email a reply'
        ));

##Overriding the default email template
The jettmail email template lives in the *jettmail/email/template* view. Simply un-register this view and override it with your own.

##Testing with the debugger
This plugin comes with a test script that triggers jettmail plugin hooks to simulate incoming email. It is located at *jettmail/test/*.
If you get an error message, it's because your database is not setup correctly, or you don't have at least PHP 5.3 or greater.

To simulate someone replying to a discussion topic from email enter the "guid" of the discussion topic in the "action guid" form, leave everything else default and then click "submit query."
You will not get a notice because of elgg's page forwarding, but if you visit the discussion topic again, you will see a new post referring to email.