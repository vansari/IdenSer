<?php
declare(strict_types=1);

namespace App\Event\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class UserPersistSubscriber implements EventSubscriberInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RegistrationEvent::class => 'createUser'
        ];
    }

    public function createUser(RegistrationEvent $event): void
    {
        $user = $event->getUser();

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}