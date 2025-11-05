<?php

namespace App\Http\Form;

use App\Domain\Attribution\Service\listAttribution;
use App\Domain\Departement\Service\getDepartement;
use App\Domain\Inventaire\Service\listInventaire;
use App\Domain\User\Service\listUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Génère un formulaire de manière automatique en lisant les propriété d'un objet.
 */
class AutomaticForm extends AbstractType
{
    public function __construct(
        private readonly getDepartement $departement,
        private readonly listInventaire $listInventaire,
        private readonly listUser  $listUser,
        private readonly listAttribution $listAttribution,
    ) {}

    final public const TYPES = [
        'string' => TextType::class,
        'int' => NumberType::class,
        'float' => NumberType::class,
        'bool' => CheckboxType::class,
        'array' => ChoiceType::class,
        'DateTime' => DateType::class, // Utiliser DateType au lieu de DateTimeType
        \DateTimeInterface::class => DateType::class, // Utiliser DateType au lieu de DateTimeType
        UploadedFile::class => FileType::class,
    ];

    final public const NAMES = [
        'name' => TextType::class,
        'description' => TextareaType::class,
        'departements' => ChoiceType::class,
        'status' => ChoiceType::class,
        'utilisateur' => ChoiceType::class,
        'actifs' => ChoiceType::class,
        'notes' => TextareaType::class,
        'dateAttribution' => DateType::class, // Ajouter explicitement
        'attribution' => ChoiceType::class, // Ajouter pour la restitution
        'etat' => ChoiceType::class, // Ajouter pour la restitution
        'checkList' => ChoiceType::class, // Ajouter pour la restitution
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];
        $refClass = new \ReflectionClass($data);
        $classProperties = $refClass->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($classProperties as $property) {
            $name = $property->getName();
            /** @var \ReflectionNamedType|null $type */
            $type = $property->getType();

            if (null === $type) {
                continue;
            }

            $typeName = $type->getName();

            // Vérifier d'abord si le nom est dans NAMES
            if (array_key_exists($name, self::NAMES)) {
                $extra = $this->getExtraProperties($typeName, $name);
                $builder->add($name, self::NAMES[$name], [
                    'required' => $this->isFieldRequired($name),
                    ...$extra,
                ]);
            }
            // Sinon vérifier le type
            elseif (array_key_exists($typeName, self::TYPES)) {
                $extra = $this->getExtraProperties($typeName, $name);
                $builder->add($name, self::TYPES[$typeName], [
                    'required' => !$type->allowsNull() && 'bool' !== $typeName,
                    ...$extra,
                ]);
            } else {
                throw new \RuntimeException(sprintf('Impossible de trouver le champs associé au type %s dans %s::%s', $typeName, $data::class, $name));
            }
        }
    }

    private function isFieldRequired(string $name): bool
    {
        $requiredFields = ['utilisateur', 'actifs', 'dateAttribution', 'attribution', 'etat'];
        return in_array($name, $requiredFields);
    }

    private function getExtraProperties(string $type, string $name): array
    {
        // Gestion des champs de type date
        if ($type === \DateTimeInterface::class || $name === 'dateAttribution') {
            return [
                'widget' => 'single_text',
                'html5' => true,
                'label' => 'Date d\'Attribution',
            ];
        }

        // Gestion des champs spécifiques par nom
        switch ($name) {
            case 'status':
                return [
                    'choices' => [
                        'Actif' => 'active',
                        'Inactif' => 'inactive',
                        'En attente' => 'pending',
                    ],
                    'placeholder' => 'Sélectionnez un statut',
                    'multiple' => false,
                ];

            case 'departements':
                $departements = $this->departement->getDepartement();
                $choices = [];
                foreach ($departements as $departement) {
                    $choices[$departement->getNom()] = $departement->getId();
                }
                return [
                    'choices' => $choices,
                    'placeholder' => 'Sélectionnez un département',
                    'label' => 'Département',
                    'multiple' => false,
                ];

            case 'utilisateur':
                $utilisateurs = $this->listUser->list();
                $choices = [];
                foreach ($utilisateurs as $utilisateur) {
                    $choices[$utilisateur->getFirstname() . ' ' . $utilisateur->getLastname()] = $utilisateur->getId();
                }
                return [
                    'choices' => $choices,
                    'placeholder' => 'Sélectionnez un utilisateur',
                    'multiple' => false,
                ];

            case 'actifs':
                $actifs = $this->listInventaire->getByStatus('stock');
                $choices = [];
                foreach ($actifs as $actif) {
                    $choices[$actif->getName() . ' (' . $actif->getSerie() . ')'] = $actif->getId();
                }
                return [
                    'choices' => $choices,
                    'placeholder' => 'Sélectionnez un actif',
                    'label' => 'Actif à attribuer',
                    'multiple' => false,
                ];

            case 'attribution':
                $attributionsActives = $this->listAttribution->list_Attribuer('attribue');
                $choices = [];
                foreach ($attributionsActives as $attribution) {
                    $label = sprintf(
                        '%s - %s (%s)',
                        $attribution->getUtilisateur()->getFirstname() . ' ' . $attribution->getUtilisateur()->getLastname(),
                        $attribution->getActif()->getName(),
                        $attribution->getActif()->getSerie()
                    );
                    $choices[$label] = $attribution->getId();
                }
                return [
                    'choices' => $choices,
                    'placeholder' => 'Sélectionnez une attribution à restituer',
                    'label' => 'Attribution à restituer',
                    'multiple' => false,
                ];

            case 'etat':
                return [
                    'choices' => [
                        'Excellent' => 'excellent',
                        'Bon' => 'bon',
                        'Moyen' => 'moyen',
                        'Mauvais' => 'mauvais',
                        'Hors service' => 'hors_service',
                    ],
                    'placeholder' => 'Sélectionnez l\'état de l\'équipement',
                    'multiple' => false,
                    'label' => 'État de l\'équipement',
                ];

            case 'checkList':
                return [
                    'choices' => [
                        'Câbles d\'alimentation inclus' => 'cables_alimentation',
                        'Accessoires fournis' => 'accessoires_fournis',
                        'Emballage d\'origine' => 'emballage_origine',
                        'Notice d\'utilisation' => 'notice_utilisation',
                        'Sans rayures apparentes' => 'sans_rayures',
                        'Fonctionnel' => 'fonctionnel',
                    ],
                    'multiple' => true,
                    'expanded' => true,
                    'label' => 'Check-list de restitution',
                ];

            case 'quantite':
                return [
                    'html5' => true,
                    'attr' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ];

            default:
                return [];
        }
    }
}
