<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Domain\Inventaire\Actifs;
use App\Domain\Inventaire\Service\listInventaire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inventaire', name: 'inventaire_')]
class ActifController extends AbstractController
{
    public function __construct(
        private readonly listInventaire $listInventaire
    )
    {
    }

    #[Route('', name: 'index')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        // Récupération des paramètres de filtrage
        $search = $request->get('search');
        $category = $request->get('category');
        $status = $request->get('status');
        $page = $request->get('page', 1);
        $limit = 10;

        // Récupération des actifs avec filtres
        if ($search || $category || $status) {
            // Utilisation de la méthode de recherche avec filtres
            $actifs = $this->listInventaire->findByFilter($search, $category, $status, $page, $limit);
            $totalActifs = count($actifs);
        } else {
            // Récupération simple paginée
            $actifs = $this->listInventaire->findBy($limit, ($page - 1) * $limit);
            $totalActifs = count($actifs);
        }

        // Statistiques
        $stats = [
            'total' => $this->listInventaire->countall(),
            'stock' => $this->listInventaire->countAllByStatus('stock'),
            'assigned' => $this->listInventaire->countAllByStatus('attribue'),
            'maintenance' => $this->listInventaire->countAllByStatus('maintenance'),
        ];

        return $this->render('actif/index.html.twig', [
            'actifs' => $actifs,
            'stats' => $stats,
            'page' => $page,
            'totalPages' => ceil($totalActifs / $limit),
            'search' => $search,
            'category' => $category,
            'status' => $status,
            'user' => $user,
        ]);
    }

    #[Route('/{slug}-{id}', name: 'detail', requirements: ['slug' => '[a-z0-9-_]+', 'id' => '\d+'], methods: ['GET'])]
    public function detail(string $slug, Request $request, Actifs $actifs): Response
    {
        if($actifs->getSlug() === $slug){
            return $this->redirectToRoute('inventaire_index');
        }

    }

}
