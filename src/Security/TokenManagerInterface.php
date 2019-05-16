<?php

namespace App\Security;


interface TokenManagerInterface
{
    function getUserByToken($token);
    public function checkAuthToken($token);
    public function generateToken($type);
    public function changeAuthTokens( string  $oldToken,string $changeToken);

}
