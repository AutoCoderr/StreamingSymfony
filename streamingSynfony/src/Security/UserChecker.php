<?php
namespace App\Security;

use App\Exception\AccountDeletedException;
use App\Security\User as AppUser;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface {
    public function checkPreAuth(UserInterface $user) {
        if ($user->getBanned() == 1) {
            throw new CustomUserMessageAuthenticationException(
                'Vous êtes bannis!'
            );
        }
    }

    public function checkPostAuth(UserInterface $user) {
        // user account is expired, the user may be notified
        /*if ($user->isExpired()) {
            throw new AccountExpiredException('...');
        }*/
    }
}