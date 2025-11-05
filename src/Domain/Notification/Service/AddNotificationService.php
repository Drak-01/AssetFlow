<?php

namespace App\Domain\Notification\Service;

use App\Domain\Notification\Notification;
use App\Domain\Notification\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class AddNotificationService
{
    public function __construct(
        private NotificationRepository $notificationRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function createStockAlertNotification(
        int $actifId,
        string $actifName,
        ?int $quantite,
        string $message
    ): Notification {
        $notification = new Notification();
        $notification->setTitle('Attribution approuvée');
        $notification->setMessage($message);
        $notification->setType('success');
        $notification->setRelatedEntity('Actifs');
        $notification->setRelatedId($actifId);
//        $notification->setCreatedAt(new \DateTimeImmutable());

        $this->notificationRepository->save($notification, true);

        return $notification;
    }
}
