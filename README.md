# Spamforce

Spamforce is a utility that scans the Exim mail queue for stuck messages.

If a stuck message contains text that the message was detected as spam by Spamassassin, it automatically strips the content and submits it to Spamcop.

Once Spamcop processes the request, it will send a notification to the registered address with a hyperlink to complete the reporting.

## Usage

A. Sign up to https://www.spamcop.net/ and get an Spamcop submit address, it will be something like:
`submit.DXtEWmmGt2VLK4lw@spam.spamcop.net`

B. `composer install eugenevdm/spamforce`

C. Copy `.env.example` to `.env` and complete these values:

```
SPAMCOP_SUBMIT=
SPAMFORCE_CC=
SPAMFORCE_FROM=
```

* Add your Spamcop submit email address
* If you want to be copied on submissions, add an administrator email address to `SPAMFORCE_CC`
* Put in some kind of legitimate `FROM:` address. It will never be used but it's good email etiquette

D. Create a new file, say `fightspam.php`:

```php
<?php

require_once 'vendor/autoload.php';

use eugenevdm\Exim\SpamForce;

$result = SpamForce::run();
```

`$result` will report how many messages are in the queue and which ones were deleted.

E. Create a cron job to run Spamforce every 5 minutes:

`*/5 * * * *     /usr/bin/php /root/fightspam.php`

## Caveats

The utility used `shell_exec` to access the Exim queue, and it might not be enabled on all systems.

To check:
`php -i | grep disabled`

Then modify `php.ini` if it's disabled. Please note this might constitute a security risk.

## Inspiration

I am a network administrator at Vander Host. Many of our clients who runs smaller businesses choose not to use the domain they have registered on our system, but rather to forward all email to upstreams such as Google and Apple. However, both Google and Apple block these messages if they contain suspect content.

At that point the messages are stuck in the Exim queue.

If you don't punish the spammers they will keep on coming back. This utility can be used to stabilize a severely affected system and bring the problem back down to a manageable level.

## Support

Contact `eugene@vander.host` or +27 82 3096710 for support
 
 https://vander.host
 VPS, Hosting and Domains
 