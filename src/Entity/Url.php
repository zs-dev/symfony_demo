<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UrlRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="url", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQ_F47645AE83360531", columns={"short_url"})})
 * @ORM\Entity(repositoryClass=UrlRepository::class)
 * @UniqueEntity(fields={"shortUrl"}, message="Short url must be unique.")
 * @ORM\HasLifecycleCallbacks
 */
class Url
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="long_url", type="text")
     * @Assert\NotBlank(message="Long url is mandatory.", groups={"Default", "NewUrl"})
     * @Assert\Length(
     *      min = 5,
     *      max = 300,
     *      minMessage = "Long url cannot be less than {{ limit }} characters.",
     *      maxMessage = "Long url cannot be more than {{ limit }} characters.",
     *      allowEmptyString = false,
     *      groups={"Default", "NewUrl"}
     * )
     */
    private $longUrl;

    /**
     * @ORM\Column(name="short_url", type="string", length=9, unique=true)
     * @Assert\NotBlank(message="Short url is mandatory.", groups={"Default"})
     * @Assert\Length(
     *      min = 5,
     *      max = 9,
     *      minMessage = "Short url cannot be less than {{ limit }} characters.",
     *      maxMessage = "Short url cannot be more than {{ limit }} characters.",
     *      allowEmptyString = false,
     *      groups={"Default"}
     * )
     */
    private $shortUrl;

    /**
     * @ORM\Column(name="date_created", type="datetime_immutable")
     */
    private $dateCreated;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLongUrl(): ?string
    {
        return $this->longUrl;
    }

    public function setLongUrl(string $longUrl): self
    {
        $this->longUrl = $longUrl;

        return $this;
    }

    public function getShortUrl(): ?string
    {
        return $this->shortUrl;
    }

    public function setShortUrl(string $shortUrl): self
    {
        $this->shortUrl = $shortUrl;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeImmutable
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeImmutable $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        if ($this->getDateCreated() instanceof \DateTimeImmutable === false) {
            $this->setDateCreated(new \DateTimeImmutable());
        }

        if (empty($this->getShortUrl())) {
            $permitted = '0123456789abcdefghijklmnopqrstuvwxyz';
            $this->setShortUrl(substr(str_shuffle($permitted), 5, 9));
        }
    }
}
