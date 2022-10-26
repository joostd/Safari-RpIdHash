<?php
include("../vendor/autoload.php");

use CBOR\CBOREncoder;

// https://www.w3.org/TR/webauthn-2/#relying-party-identifier
// RP ID - a valid domain string identifying the WebAuthn Relying Party 
// By default, the RP ID for a WebAuthn operation is set to the caller’s origin's effective domain.

// An RP ID is based on a host's domain name. It does not itself include a scheme or port, as an origin does. 
$rpId = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
$hash = hash('sha256', $rpId);
error_log("sha256($rpId) = " . $hash);

// get the rpIdHash from the attestationObject's authData (first 32 bytes)
$bin = hex2bin($_POST['attestationObject']);
$attestationObject = CBOREncoder::decode($bin,true);
$authData = $attestationObject['authData']->get_byte_string();
// rpIdHash (32 bytes): SHA-256 hash of the RP ID the credential is scoped to.
$rpIdHash = substr($authData,0,32); // eat 32 byte hash

error_log( 'rpIdHash:' . bin2hex($rpIdHash) );

// https://www.w3.org/TR/webauthn-2/#sctn-registering-a-new-credential
// 13. Verify that the rpIdHash in authData is the SHA-256 hash of the RP ID expected by the Relying Party.

//assert($hash === $rpIdHash);

echo '<li><b>sha256('.$rpId.'):</b>' . $hash;
echo '<li><b>rpIdHash:</b>' . bin2hex($rpIdHash);
echo "<hr/><a href='/'>again</a>";
