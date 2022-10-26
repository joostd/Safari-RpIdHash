# Safari RpIdHash

Simple, quick & dirty WebAuthn demo to illustrate an issue with Safari 16.0, 16.1 (and possibly earlier versions).

## Install locally

As the issue is with Safari, we only provide instructions for MacOS.

The server is implemented with PHP. As PHP is no longer distributed with Macos (at least not with Macos 12 and 13), use <a href="brew.sh">brew.sh</a> to install it:

    brew install php

Also required is <a href="https://getcomposer.org">Composer</a>, as the server uses <a href="https://github.com/2tvenom/CBOREncode">2tvenom/CBOREncode</a> for decoding CBOR data.

    brew install composer

To run on your local system, simply clone this repository and run composer to install the dependency:

    composer install

Then use the PHP builtin web server to run the web application:

    php -S localhost:8000 -t www

## Testing a non-localhost RP ID

To test with an RP ID other than localhost, use a reverse proxy on an HTTPS endpoint that tunnels back to your localhost.
See <a href="https://github.com/anderspitman/awesome-tunneling">here</a> for some options.

As an example, install <a href="https://ngrok.com/">ngrok</a>:

    brew install ngrok

and run a reverse proxy using:

    ngrok http 8000

where the local port number should match your PHP server's listening port.

## Testing without a server.

You can also test without a server, but you'd need to put the <a href="index.html">index.html</a> file on an HTTPS site.
Instead of submitting the form with `attestationObject` data, click the link to <a href="cbor.me">cbor.me</a> to decode your browser's `attestationObject` and observe the first 32 bytes (i.e. first 64 hex digits) from the `authData` component.

