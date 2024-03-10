<?php
declare(strict_types=1);

namespace App\Event\User;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class PasswordHasherSubscriber implements EventSubscriberInterface
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            RegistrationEvent::class => 'hashPassword'
        ];
    }

    public function hashPassword(RegistrationEvent $event): void
    {
        $user = $event->getUser();

        if (!$user->getPlainPassword()) {
            throw new \UnexpectedValueException('user_reg_password_empty');
        }

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $user->getPlainPassword()
        );

        $user->setPassword($hashedPassword);
        $user->eraseCredentials();
    }
}