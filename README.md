# Safari RpIdHash

Simple, quick & dirty WebAuthn demo to illustrate an issue with Safari 16.0, 16.1 (and possibly earlier versions).

## The issue

There seems to be an issue with Apple's Safari browser implementation of WebAuthn.
When using a U2F (CTAP1) security key, the WebAuthn `navigator.credential.create` call returns an attestation object with a non-matching RP Id Hash.
You can track this issue on the [Webkit Bugzilla](https://bugs.webkit.org/show_bug.cgi?id=247344).

### What is an RP ID Hash?

The Relying Party (RP) ID is an identifier for the site you are registering your FIDO credential.
By default, it is the domain name of the website the WebAuthn API calls originate from.
The rpIdHash is a 32 byte value containing the SHA-256 hash of this RP ID.

See the Webauthn spec's definition of <a href="https://www.w3.org/TR/webauthn-2/#relying-party-identifier">relying-party-identifier</a>:

### What is the problem?

The problem arises when the RP verifies the RpIdHash when for instance registering a new credential:
See the WebAuthn spec for the procedure for <a href="https://www.w3.org/TR/webauthn-2/#sctn-registering-a-new-credential">registering-a-new-credential</a>:

- 13 Verify that the rpIdHash in authData is the SHA-256 hash of the RP ID expected by the Relying Party.

### When does this problem occur?

Note that this issue occurs only with U2F (CTAP1) security keys.
When using a FIDO2 security key (CTAP2) the resulting RpIdHash is correct.
There seems to be [another bug in Safari](https://bugs.webkit.org/show_bug.cgi?id=231043) however, that will intermittently prevent you from using CTAP2.
When using a CTAP1+CTAP2 security key like a Yubikey (5 series), it may fallback to CTAP1 and the RP ID Hash issue will be triggered.
If you disable CTAP1 or use a CTAP2-only security key, Safari may not recognize your security key and you can only cancel out of the modal Webauthn dialog to proceed.

So to trigger this issue, use a U2F-only key like a YubiKey 4 or Google's Titan security key.
With a YubiKey 5, you can disable FIDO2 to enforce the use of CTAP1 using <a href="https://developers.yubico.com/yubikey-manager/">ykman</a>:

    ykman config usb --disable FIDO2

## Some obervations

Note that

- as stated above: the issue does not seem to occur with FIDO2 (CTAP2) security keys (unless the CTAP2-bug also occurs, see the remark above)).
- the issue is observed with Safari versions 16.0 and 16.1, and may possible be present on older versions as well.
- the issue is not observed with any other browsers I tested (Google Chrome, Miscrosoft Edge, Firefox and Brave).
- the issue is also not obverved with Mobile Safari (tested on iOS 16.1).
- in my tests when there is a mismatch I always see the RpIdHash value `a54672b222c4cf95e151ed8d4d3c767a6cc349435943794e884f3d023a8229fd` returned from the security key.
- some users have reported intermittend occurences of the issue.

## Running the demo

The simplest way to run the demo is to point your Safari browser to <a href="https://rpid.joostd.nl">rpid.joostd.nl</a> and click the `navigator.credentials.create` button.
After completing the registration ceremony, the RP ID Hash from your security key is compared to the SHA-256 hash of the RP ID (i.e. the hostname).

### Run locally

As the issue is with Safari, we only provide instructions for MacOS.

To run on your local system, simply clone this repository and run:

    python3 -m http.server 8000

Point your browser to http://localhost:8000 to view the demo.

### Testing a non-localhost RP ID

To test with other RP IDs, you will need to deploy on an HTTPS endpoint.
As the demo only requires JavaScript to run (i.e. without any server-side processing) you can host it on any HTTPS web site.

#### Testing  using a reverse proxy

You can also use a reverse proxy on an HTTPS endpoint that tunnels back to your localhost.
See <a href="https://github.com/anderspitman/awesome-tunneling">here</a> for some options.

As an example, install <a href="https://ngrok.com/">ngrok</a>:

    brew install ngrok

and run a reverse proxy using:

    ngrok http 8000

where the local port number should match your PHP server's listening port.
Then point your browser to the HTTPS URL generated by ngrok to run the demo through the reverse proxy on your local machine.
Note that by default *anyone* can connect to that URL so be careful and kill your tunnel immediately after testing.

## Other sites that demonstrate the issue

There are many other sites that suffer from this issue.
You can find some examples below.

### Yubico demo website

Point your browser at [https://demo.yubico.com/webauthn-technical/registration](https://demo.yubico.com/webauthn-technical/registration) and use your security key to register a FIDO credential.
When the RP ID Hash mismatch occurs, you will get an error message: `Wrong RP ID hash in response.`

###  Duo demo site

Point your browser at [webauthn.io](https://webauthn.io/) and click _Advanced Settings_.
In the Registration Settings, Uncheck "Require User Verification" and select "Cross-Platform" as Authenticator Attachment.
Then click "Register" and use your security key to register a FIDO credential.

When the RP ID Hash mismatch occurs, you will get an error message: `Registration failed: Unexpected RP ID hash`.

