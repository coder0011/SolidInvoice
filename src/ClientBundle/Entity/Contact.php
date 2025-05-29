<?php

declare(strict_types=1);

/*
 * This file is part of SolidInvoice project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\ClientBundle\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Serializable;
use SolidInvoice\ClientBundle\Repository\ContactRepository;
use SolidInvoice\CoreBundle\Traits\Entity\CompanyAware;
use SolidInvoice\CoreBundle\Traits\Entity\TimeStampable;
use SolidInvoice\InvoiceBundle\Entity\Invoice;
use SolidInvoice\InvoiceBundle\Entity\RecurringInvoice;
use SolidInvoice\QuoteBundle\Entity\Quote;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Serializer\Annotation as Serialize;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;
use function strtolower;

/*#[ApiResource(
    types: ['https://schema.org/Person'],
    sunset: 'V2.4.0',
    deprecationReason: 'This resource is deprecated, please use the `/clients/{clientId}/contacts` endpoint',
    normalizationContext: [
        'groups' => ['contact_api'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => false,
    ],
    denormalizationContext: [
        'groups' => ['contact_api'],
    ],
)]*/
/*#[ApiResource(
    uriTemplate: '/clients/{clientId}/contacts',
    operations: [
        new GetCollection(),
        new Post(),
    ],
    uriVariables: [
        'clientId' => new Link(
            fromProperty: 'contacts',
            fromClass: Client::class
        ),
    ],
    normalizationContext: [
        'groups' => ['contact_api'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => false,
    ],
    denormalizationContext: [
        'groups' => ['contact_api'],
    ]
)]
#[ApiResource(
    uriTemplate: '/clients/{clientId}/contact/{id}',
    operations: [
        //new Get(),
        new Patch(),
        new Delete(),
    ],
    uriVariables: [
        'clientId' => new Link(
            toProperty: 'client',
            fromClass: Client::class
        ),
        'id' => new Link(
            fromClass: Contact::class
        ),
    ],
    normalizationContext: [
        'groups' => ['contact_api'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => false,
    ],
    denormalizationContext: [
        'groups' => ['contact_api'],
    ]
)]*/
#[ApiResource(
    uriTemplate: '/clients/{clientId}/contacts',
    operations: [ new GetCollection(), new Post() ],
    uriVariables: [
        'clientId' => new Link(
            fromProperty: 'contacts',
            fromClass: Client::class,
        ),
    ],
    normalizationContext: [
        'groups' => ['contact_api:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => false,
    ],
    denormalizationContext: [
        'groups' => ['contact_api:write'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => false,
    ]
)]
#[ApiResource(
    uriTemplate: '/clients/{clientId}/contact/{id}',
    operations: [new Get(), new Delete(), new Patch()],
    uriVariables: [
        'clientId' => new Link(
            fromProperty: 'contacts',
            fromClass: Client::class,
        ),
        'id' => new Link(
            fromClass: Contact::class,
        ),
    ],
    normalizationContext: [
        'groups' => ['contact_api:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => false,
    ],
    denormalizationContext: [
        'groups' => ['contact_api:write'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => false,
    ]
)]
#[ORM\Table(name: Contact::TABLE_NAME)]
#[ORM\Index(columns: ['email'])]
#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact implements Serializable, Stringable
{
    final public const TABLE_NAME = 'contacts';

    use TimeStampable;
    use CompanyAware;

    #[ORM\Column(name: 'id', type: UlidType::NAME)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[Serialize\Groups(['contact_api:read'])]
    private ?Ulid $id = null;

    #[ApiProperty(iris: ['https://schema.org/givenName'])]
    #[ORM\Column(name: 'firstName', type: Types::STRING, length: 125)]
    #[Assert\NotBlank(groups: ['Default', 'form'])]
    #[Assert\Length(max: 125, groups: ['Default', 'form'])]
    #[Serialize\Groups(['contact_api:read', 'contact_api:write'])]
    private ?string $firstName = null;

    #[ApiProperty(iris: ['https://schema.org/familyName'])]
    #[ORM\Column(name: 'lastName', type: Types::STRING, length: 125, nullable: true)]
    #[Assert\Length(max: 125, groups: ['Default', 'form'])]
    #[Serialize\Groups(['contact_api:read', 'contact_api:write'])]
    private ?string $lastName = null;

    #[ApiProperty(
        writable: false,
        writableLink: false,
        example: '/api/clients/3fa85f64-5717-4562-b3fc-2c963f66afa6',
        iris: ['https://schema.org/Organization']
    )]
    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'contacts')]
    #[ORM\JoinColumn(name: 'client_id')]
    #[Serialize\Groups(['contact_api:read', 'contact_api:write'])]
    #[Assert\Valid]
    #[Assert\NotBlank]
    private ?Client $client = null;

    #[ApiProperty(iris: ['https://schema.org/email'])]
    #[ORM\Column(name: 'email', type: Types::STRING, length: 255)]
    #[Assert\NotBlank(groups: ['Default', 'form'])]
    #[Assert\Email(mode: Assert\Email::VALIDATION_MODE_STRICT, groups: ['Default', 'form'])]
    #[Serialize\Groups(['contact_api:read', 'contact_api:write'])]
    private ?string $email = null;

    /**
     * @var Collection<int, AdditionalContactDetail>
     */
    #[ORM\OneToMany(mappedBy: 'contact', targetEntity: AdditionalContactDetail::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Assert\Valid]
    // #[Serialize\Groups(['contact_api:read', 'contact_api:write'])]
    /*#[ApiProperty(
        openapiContext: [
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'properties' => [
                    'type' => [
                        'type' => 'string',
                    ],
                    'value' => [
                        'type' => 'string',
                    ],
                ],
            ],
        ],
        extraProperties: [
            SchemaPropertyMetadataFactory::JSON_SCHEMA_USER_DEFINED => true,
        ],
    )]*/
    private Collection $additionalContactDetails;

    /**
     * @var Collection<int, Invoice>
     */
    #[ORM\ManyToMany(targetEntity: Invoice::class, mappedBy: 'users')]
    private Collection $invoices;

    /**
     * @var Collection<int, RecurringInvoice>
     */
    #[ORM\ManyToMany(targetEntity: RecurringInvoice::class, mappedBy: 'users')]
    private Collection $recurringInvoices;

    /**
     * @var Collection<int, Quote>
     */
    #[ORM\ManyToMany(targetEntity: Quote::class, mappedBy: 'users')]
    private Collection $quotes;

    public function __construct()
    {
        $this->additionalContactDetails = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->recurringInvoices = new ArrayCollection();
        $this->quotes = new ArrayCollection();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function addAdditionalContactDetail(?AdditionalContactDetail $detail): self
    {
        if ($detail !== null) {
            $this->additionalContactDetails->add($detail);
            $detail->setContact($this);
        }

        return $this;
    }

    public function removeAdditionalContactDetail(AdditionalContactDetail $detail): self
    {
        $this->additionalContactDetails->removeElement($detail);

        return $this;
    }

    /**
     * @return Collection<int, AdditionalContactDetail>
     */
    public function getAdditionalContactDetails(): Collection
    {
        return $this->additionalContactDetails;
    }

    public function getAdditionalContactDetail(string $type): ?AdditionalContactDetail
    {
        $type = strtolower($type);
        foreach ($this->additionalContactDetails as $detail) {
            if (strtolower((string) $detail->getType()) === $type) {
                return $detail;
            }
        }

        return null;
    }

    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    /**
     * @return array<string, string|DateTimeInterface|Ulid|null>
     */
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'created' => $this->created,
            'updated' => $this->updated,
        ];
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        $this->__unserialize(unserialize($serialized));
    }

    /**
     * @param array<string, string|DateTimeInterface|Ulid|null> $data
     */
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->firstName = $data['firstName'];
        $this->lastName = $data['lastName'];
        $this->email = $data['email'];
        $this->created = $data['created'];
        $this->updated = $data['updated'];
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    /**
     * @return Collection<int, RecurringInvoice>
     */
    public function getRecurringInvoices(): Collection
    {
        return $this->recurringInvoices;
    }

    /**
     * @return Collection<int, Quote>
     */
    public function getQuotes(): Collection
    {
        return $this->quotes;
    }

    public function __toString(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
