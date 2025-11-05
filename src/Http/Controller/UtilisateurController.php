<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Domain\Attribution\Attribution;
use App\Domain\Attribution\DTO\createAttributionDTO;
use App\Domain\Attribution\Service\approuveattribution;
use App\Domain\Attribution\Service\createAttribution;
use App\Domain\Attribution\Service\listAttribution;
use App\Domain\Attribution\Service\revokeattribution;
use App\Domain\Departement\Service\getDepartement;
use App\Domain\Inventaire\Service\listInventaire;
use App\Domain\Restitution\DTO\CreateRestitutionDTO;
use App\Domain\Restitution\Service\CreateRestitutionService;
use App\Domain\Restitution\Service\listRestitution;
use App\Domain\User\DTO\CreateDTO;
use App\Domain\User\Service\createUserService;
use App\Domain\User\Service\deleteService;
use App\Domain\User\Service\listUser;
use App\Domain\User\Utilisateur;
use App\Http\Form\AutomaticForm;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/utilisateur', name: 'utilisateur_')]
class UtilisateurController extends AbstractController
{
    public function __construct(
        private readonly createUserService $createUserService,
        private readonly listUser $listUser,
        private readonly getDepartement $departement,
        private readonly deleteService  $deleteService,
        private readonly listInventaire  $listInventaire,
        private readonly listAttribution  $listAttribution,
        private readonly createAttribution $createAttribution,
        private readonly approuveattribution $approuveattribution,
        private readonly revokeAttribution $revokeAttribution,
        private readonly CreateRestitutionService $createRestitutionService,
        private readonly listRestitution   $listRestitution,
    )
    {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        $user = $this->getUser();
        //LIste des utilisateurs
        $users = $this->listUser->list();

        //create DTO
        $dto = new CreateDTO();
        $form = $this->createForm(AutomaticForm::class, $dto, [
            'action' => $this->generateUrl('utilisateur_create'),
        ]);

        return $this->render('utilisateur/index.html.twig', [
            'form' => $form,
            'users' => $users,
            'total' => count($users),
            'active' => $this->listUser->active(),
            'inactive' => $this->listUser->inactive(),
            'user' => $user,
        ]);
    }

    #[Route('/user', name: 'create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $dto = new CreateDTO();
        $form = $this->createForm(AutomaticForm::class, $dto);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->createUserService->createUser($dto);
            $this->addFlash('success', 'Utilisateurs Crée avec Succès');
            return $this->redirectToRoute('utilisateur_index');
        }

        $this->addFlash('danger', 'Erreur lors de l\'ajoute de l\'utilisateur');

        return $this->redirectToRoute('utilisateur_index');
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Utilisateur $user,  CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $token = $request->request->get('_token');
        $expectedToken = $csrfTokenManager->getToken('delete-user-' . $user->getId());

        if (!$csrfTokenManager->isTokenValid($expectedToken)) {
            $this->addFlash('error', 'Token de sécurité expiré ou invalide');
            return $this->redirectToRoute('utilisateur_index');
        }

        try {
            $this->deleteService->delete($user);
            $this->addFlash('success', 'Utilisateur supprimé avec succès!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }

        return $this->redirectToRoute('utilisateur_index');
    }
    //--------------------- Restitution --------------------------
    #[Route('/restitution', name: 'restitution', methods: ['GET', 'POST'])]
    public  function restitution(Request $request):Response
    {
        //------------------- Les variables pour la restitution ------------
        $restitutions = $this->listRestitution->lists();
        $stats = $this->listRestitution->stats();

        //-----------------------------------
        $dto = new CreateRestitutionDTO();
        $form = $this->createForm(AutomaticForm::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->createRestitutionService->create($dto);
                $this->addFlash('success', 'Restitution créée avec succès !');
                return $this->redirectToRoute('utilisateur_restitution');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la restitution : ' . $e->getMessage());
            }
        }

        $user = $this->getUser();
        return $this->render('utilisateur/restitution.html.twig', [
            'form' => $form->createView(),
            'restitutions' => $restitutions,
            'stats' => $stats,
            'user' => $user,

        ]);
    }

    //----------------------------------------------------
    #[Route('/attribution', name: 'attribution', methods: ['POST', 'GET'])]
    public function attribution(Request $request): Response
    {
        // Récupérer les données nécessaires
        $actifsAttribues = $this->listInventaire->getByStatus('attribue');
        $attributions = $this->listAttribution->getAttributions();

        // Calculer les statistiques
        $stats = $this->calculateStats($attributions, $actifsAttribues);

        $dto = new createAttributionDTO();
        $form = $this->createForm(AutomaticForm::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $attribution = $this->createAttribution->create($dto);
                $this->addFlash('success', 'Attribution créée avec succès !');
                return $this->redirectToRoute('utilisateur_attribution');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création : ' . $e->getMessage());
            }
        }

        $user = $this->getUser();
        return $this->render('utilisateur/attribution.html.twig', [
            'form' => $form->createView(),
            'actifs' => $actifsAttribues,
            'stats' => $stats,
            'attributions' => $attributions,
            'user' => $user,
        ]);
    }

    #[Route('/attribution/{id}/revoke', name: 'attribution_revoke', methods: ['POST'])]
    public function revokeAttribution(Attribution $attribution, Request $request): Response
    {
        if(!$attribution){
            $this->addFlash('danger', 'Attribution n\'existe pas');
        }
        try {
            $this->revokeAttribution->revoke($attribution);
            $this->addFlash('success', 'Attribution révoquée avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la révocation : ' . $e->getMessage());
        }

        return $this->redirectToRoute('utilisateur_attribution');
    }

    #[Route('/attribution/{id}/approve', name: 'attribution_approve', methods: ['POST'])]
    public function approveAttribution(Attribution $attribution, Request $request): Response
    {
        try {
            $this->approuveattribution->approve($attribution);
            $this->addFlash('success', 'Attribution approuvée avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'approbation : ' . $e->getMessage());
        }

        return $this->redirectToRoute('utilisateur_attribution');
    }

    /**
     * Calcule les statistiques pour le tableau de bord
     */
    private function calculateStats(array $attributions, array $actifsAttribues): array
    {
        $now = new \DateTime();
        $debutMois = new \DateTime('first day of this month');

        // Attributions ce mois
        $attributionsCeMois = array_filter($attributions, function($attribution) use ($debutMois) {
            return $attribution->getDateAttribution() >= $debutMois;
        });

        // En attente
        $enAttente = array_filter($attributions, function($attribution) {
            return $attribution->getStatus() === 'pending';
        });

        // Actifs disponibles (stock)
        $actifsDisponibles = $this->listInventaire->getByStatus('stock');

        // Total des actifs
        $totalActifs = count($actifsDisponibles) + count($actifsAttribues);

        // Taux d'occupation
        $tauxOccupation = $totalActifs > 0 ?
            round((count($actifsAttribues) / $totalActifs) * 100) : 0;

        return [
            'ceMois' => count($attributionsCeMois),
            'enAttente' => count($enAttente),
            'actifsDisponibles' => count($actifsDisponibles),
            'tauxOccupation' => $tauxOccupation . '%',
            'totalActifs' => $totalActifs,
            'actifsAttribues' => count($actifsAttribues),
        ];
    }
}
