<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Domain\Notification\Service\StockAlert;
use App\Domain\Notification\Service\listNotification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NotificationController extends AbstractController
{
    public function __construct(
        private readonly StockAlert $stockAlert,
        private readonly ListNotification $listNotification
    ) {
    }

    #[Route('/notification', name: 'notification_index')]
    public function index(): JsonResponse
    {
        // Vérifier et créer les alertes de stock
        $this->stockAlert->stockAlert();

        // Récupérer les notifications récentes
        $notifications = $this->listNotification->listsRecent();

        return new JsonResponse($notifications);
    }


    #[Route('/api/notifications', name: 'api_notifications', methods: ['GET'])]
    public function getNotifications(): JsonResponse
    {
        // Vérifier les alertes de stock avant de récupérer les notifications
        $alertsCreated = $this->stockAlert->checkAndCreateAlerts();

        $notifications = $this->listNotification->lists();

        $data = [
            'notifications' => $notifications,
            'alerts_created' => $alertsCreated,
            'total' => count($notifications),
            'unread_count' => $this->listNotification->countUnread()
        ];

        return $this->json($data);
    }

    #[Route('/api/notifications/recent', name: 'api_notifications_recent', methods: ['GET'])]
    public function getRecentNotifications(): JsonResponse
    {
        // Vérifier les alertes de stock
        $this->stockAlert->checkAndCreateAlerts();

        $notifications = $this->listNotification->listsRecent(5);

        return $this->json([
            'notifications' => $notifications,
            'total' => count($notifications),
            'unread_count' => $this->listNotification->countUnread()
        ]);
    }

    #[Route('/api/notifications/unread', name: 'api_notifications_unread', methods: ['GET'])]
    public function getUnreadNotifications(): JsonResponse
    {
        $notifications = $this->listNotification->listsUnread();

        return $this->json([
            'notifications' => $notifications,
            'count' => count($notifications)
        ]);
    }

    #[Route('/api/notifications/stock-alerts', name: 'api_notifications_stock_alerts', methods: ['GET'])]
    public function getStockAlerts(): JsonResponse
    {
        // Vérifier et créer les alertes de stock
        $alertsCreated = $this->stockAlert->checkAndCreateAlerts();
        $lowStockItems = $this->stockAlert->getLowStockActifs();

        $stockAlerts = $this->listNotification->listsStockAlerts();

        return $this->json([
            'alerts' => $stockAlerts,
            'alerts_created' => $alertsCreated,
            'low_stock_items' => count($lowStockItems),
            'items' => array_map(function($item) {
                return [
                    'id' => $item->getId(),
                    'name' => $item->getName(),
                    'quantite' => $item->getQuantite()
                ];
            }, $lowStockItems)
        ]);
    }

    #[Route('/api/notifications/mark-read/{id}', name: 'api_notifications_mark_read', methods: ['POST'])]
    public function markAsRead(int $id): JsonResponse
    {
        try {
            $success = $this->listNotification->markAsRead($id);

            if (!$success) {
                return $this->json(['error' => 'Notification non trouvée'], Response::HTTP_NOT_FOUND);
            }

            return $this->json([
                'success' => true,
                'unread_count' => $this->listNotification->countUnread()
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/notifications/mark-all-read', name: 'api_notifications_mark_all_read', methods: ['POST'])]
    public function markAllAsRead(): JsonResponse
    {
        try {
            $count = $this->listNotification->markAllAsRead();

            return $this->json([
                'success' => true,
                'marked' => $count,
                'unread_count' => 0
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/notifications/stats', name: 'api_notifications_stats', methods: ['GET'])]
    public function getStats(): JsonResponse
    {
        $stats = $this->listNotification->getStats();

        return $this->json($stats);
    }

    #[Route('/api/notifications/check-stock', name: 'api_notifications_check_stock', methods: ['POST'])]
    public function checkStock(): JsonResponse
    {
        try {
            $alertsCreated = $this->stockAlert->checkAndCreateAlerts();
            $lowStockItems = $this->stockAlert->getLowStockActifs();

            return $this->json([
                'success' => true,
                'alerts_created' => $alertsCreated,
                'low_stock_items' => count($lowStockItems),
                'unread_count' => $this->listNotification->countUnread()
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
