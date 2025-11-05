<?php

namespace App\Domain\Notification\Service;

use App\Domain\Notification\NotificationRepository;

readonly class listNotification
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository
    ) {
    }

    public function lists(): array
    {
        return $this->notificationRepository->findBy([], ['createdAt' => 'DESC']);
    }

    public function listsRecent(int $limit = 5): array
    {
        return $this->notificationRepository->findBy([], ['createdAt' => 'DESC'], $limit);
    }

    public function listsUnread(): array
    {
        return $this->notificationRepository->findUnread();
    }

    public function listsStockAlerts(): array
    {
        return $this->notificationRepository->findUnreadStockAlerts();
    }

    public function countUnread(): int
    {
        return $this->notificationRepository->countUnread();
    }

    public function markAsRead(int $id): bool
    {
        $notification = $this->notificationRepository->find($id);

        if (!$notification) {
            return false;
        }

        $notification->markAsRead();
        $this->notificationRepository->save($notification, true);

        return true;
    }

    public function markAllAsRead(): int
    {
        return $this->notificationRepository->markAllAsRead();
    }

    public function getStats(): array
    {
        $total = $this->notificationRepository->count([]);
        $unread = $this->notificationRepository->countUnread();
        $types = $this->notificationRepository->countByType();

        return [
            'total' => $total,
            'unread' => $unread,
            'types' => $types
        ];
    }
}
