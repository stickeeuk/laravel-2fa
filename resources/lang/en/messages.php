<?php

return [
    'cancel' => 'Cancel',
    'confirm' => 'Confirm',
    'register' => [
        'heading' => 'Register for Two Factor Authentication',
        'intro' => 'Two factor authentication (2FA) strengthens access security by requiring two methods
            (also referred to as factors) to verify your identity.
            Two factor authentication protects against phishing, social engineering and password brute force attacks
            and secures your logins from attackers exploiting weak or stolen credentials.',
        'choose' => 'Please choose a method.',
        'recovery' => 'If you need to log in without your authenticator, you can use these one-time recovery codes.
            Please copy these somewhere secure. They will not be displayed again.',
        'recovery-after' => 'When you have saved your recovery codes, please continue:',
        'register-with' => 'Register with :name',
    ],
    'authenticate' => [
        'heading' => 'Two Factor Authentication',
        'intro' => 'Enter the pin from Google Authenticator 2FA or a recovery code.',
        'code' => 'One Time Password / Recovery Code',
        'submit' => 'Authenticate',
        'cooldown' => [
            'message' => 'We can only send an activation code every [cooldown_value] [cooldown_measurement].
                Please wait another [available_at_value] [available_at_measurement] before trying again.',
        ],
    ],
];
