# Spamforce

Spamforce is a utility that scans the Exim mail queue for stuck messages.

If a stuck message contains notification that the message was detected as spam by Spamassassin, it automatically strips the content and submits it to Spamcop.

Once Spamcop processes the request, it will send a notification to the registered address with a hyperlink to complete the reporting.

## Usage

Sign up to https://www.spamcop.net/

Find your Spamcop submit address, it will be something like:
`submit.DXtEWmmGt2VLK4lw@spam.spamcop.net`

Copy `.env.example` to `.env` and:

* Add your Spamcop submit address to the .env file.
* Add your administrator email address to the .env file.

Create a cron job to run Spamforce every 5 minutes:

*/5 * * * *     /usr/bin/php /root/spamforce.php

## Caveats

The utility used `shell_exec` to access the Exim queue, and it might not be enabled on all systems.

To check:
`php -i | grep disabled`

Then modify `php.ini` if it's disabled. Please note this might constitute a security risk.

## Support

Contact `eugene@vander.host` or +27 82 3096710 for support
 
 https://vander.host
 VPS, Hosting and Domains
 