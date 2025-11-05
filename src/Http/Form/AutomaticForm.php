<?php

namespace App\Http\Form;

use App\Domain\Departement\Service\getDepartement;
use App\Domain\Inventaire\Service\listInventaire;
use App\Domain\User\Service\listUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
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
    )
    {
    }

    final public const TYPES = [
        'string' => TextType::class,
        'int' => NumberType::class,
        'float' => NumberType::class,
        'bool' => CheckboxType::class,
        'array' => ChoiceType::class,
        'DateTime' => DateTimeType::class,
        \DateTimeInterface::class => DateTimeType::class,
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
            $extra = $this->getExtraProperties($typeName, $name);

            if (array_key_exists($name, self::NAMES)) {
                $builder->add($name, self::NAMES[$name], [
                    'required' => false,
                    ...$extra,
                ]);
            } elseif (array_key_exists($typeName, self::TYPES)) {
                $builder->add($name, self::TYPES[$typeName], [
                    'required' => !$type->allowsNull() && 'bool' !== $typeName,
                    ...$extra,
                ]);
            } else {
                throw new \RuntimeException(sprintf('Impossible de trouver le champs associé au type %s dans %s::%s', $typeName, $data::class, $name));
            }
        }
    }

    private function getExtraProperties(string $type, string $name): array
    {
        if ($type === \DateTimeInterface::class) {
            return [
                'input' => 'datetime_immutable',
            ];
        }

        if ($name === 'status') {
            return [
                'choices' => [
                    'Actif' => 'active',
                    'Inactif' => 'inactive',
                    'En attente' => 'pending',
                ],
                'placeholder' => 'Sélectionnez un statut',
                'required' => true,
                'multiple' => false,
            ];
        }

        if ($name === 'departements') { // Corriger le nom (avec 's')
            $departements = $this->departement->getDepartement();

            $choices = [];
            foreach ($departements as $departement) {
                $choices[$departement->getNom()] = $departement->getId();
            }

            return [
                'choices' => $choices,
                'placeholder' => 'Sélectionnez un département',
                'required' => true,
                'label' => 'Département',
                'multiple' => false,
            ];
        }

        // Dans votre AutomaticForm, modifiez la section 'utilisateurs' et 'actifs' :

        if($name === 'utilisateur') { // Corriger le nom pour correspondre au template
            $utilisateurs = $this->listUser->list();

            $choices = [];
            foreach ($utilisateurs as $utilisateur) {
                $choices[$utilisateur->getFirstname()] = $utilisateur->getId(); // Utiliser une méthode getFullName()
            }

            return [
                'choices' => $choices,
                'placeholder' => 'Sélectionnez un utilisateur',
                'required' => true,
                'multiple' => false,
            ];
        }

        if ($name === 'actifs') {
            $actifs = $this->listInventaire->getByStatus('stock');

            $choices = [];
            foreach ($actifs as $actif) {
                $choices[$actif->getSerie()] = $actif->getId();
            }

            return [
                'choices' => $choices,
                'placeholder' => 'Sélectionnez un actif',
                'required' => true,
                'label' => 'Actif à attribuer',
            ];
        }

        if ($name === 'dateAttribution') {
            return [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => true,
                'label' => 'Date d\'Attribution',
                'html5' => true,
            ];
        }

        return [];
    }
}
