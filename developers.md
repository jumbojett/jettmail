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
The jettmail email template lives in the *jettmail/email/template* view simply unregister this view and override it with your own.
