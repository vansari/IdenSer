<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\User\RegistrationEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/')]
class UserController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {

    }

    #[Route('/registration', name: 'app_user_registration', methods: ['POST'])]
    public function register(Request $request, ValidatorInterface $validator, SerializerInterface $serializer, EventDispatcherInterface $userRegistrationEventDispatcher): Response
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json', context: ['groups' => ['user:create']]);
        $errors = $validator->validate($user, groups: ['user:create']);

        if ($errors->count()) {
            $message = $this->translator->trans('validation_errors', domain: 'validators') . PHP_EOL;
            /** @var ConstraintViolationInterface $error */
            foreach ($errors as $error) {
                $message .= $error->getMessage() . PHP_EOL;
            }
            throw new UnprocessableEntityHttpException($message);
        }

        $userRegistrationEventDispatcher->dispatch(new RegistrationEvent($user));

        $serialized = $serializer->serialize($user, 'json', context: ['groups' => ['user:read']]);

        return new JsonResponse(
            $serialized,
            status: Response::HTTP_CREATED,
            json: true
        );
    }
}
