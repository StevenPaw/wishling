<?php

namespace App\Auth;

use RuntimeException;

/**
 * Fachlicher Fehler im Login-Ablauf. Der Code ist der HTTP-Statuscode.
 */
class AuthException extends RuntimeException
{
}
