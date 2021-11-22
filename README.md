# codeigniter-gmail-client-api

Using Codeigniter with integration of Gmail Client API in php.

## Ability to recieve and send email using Gmail client API.


## Installation

 - Clone Github repository
 - My application uses Mailparser and it requires extenstion php_mailparse.dll (windows) and I have downloaded from https://pecl.php.net/package/mailparse/3.0.4/windows
    - Also, add it to php.ini file after mbstring extension and restart the server.
 - Run composer install
 - Setup a domain to browser this application for ease.

## Configuration

 - Go to https://YOURLOCALDOMAIN.com/settings
 - Fill in Google Name from, Google Email From, Google Client ID and Google Client Secret.
 - Make sure you have enabled the Gmail API in your Google API Console.
 - Go to https://YOURLOCALDOMAIN.com/google/settings
 - You can find url to and links to guide to open Google API Console and validate your application.

