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
 * @UniqueEntity(fields={"short_url"}, message="Short url must be unique.")
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
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="Long url is mandatory.")
     * @Assert\Length(
     *      min = 5,
     *      max = 300,
     *      minMessage = "Long url cannot be less than {{ limit }} characters.",
     *      maxMessage = "Long url cannot be more than {{ limit }} characters.",
     *      allowEmptyString = false
     * )
     */
    private $long_url;

    /**
     * @ORM\Column(type="string", length=9, unique=true)
     * @Assert\NotBlank(message="Short url is mandatory.")
     * @Assert\Length(
     *      min = 5,
     *      max = 9,
     *      minMessage = "Short url cannot be less than {{ limit }} characters.",
     *      maxMessage = "Short url cannot be more than {{ limit }} characters.",
     *      allowEmptyString = false
     * )
     */
    private $short_url;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $date_created;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLongUrl(): ?string
    {
        return $this->long_url;
    }

    public function setLongUrl(string $long_url): self
    {
        $this->long_url = $long_url;

        return $this;
    }

    public function getShortUrl(): ?string
    {
        return $this->short_url;
    }

    public function setShortUrl(string $short_url): self
    {
        $this->short_url = $short_url;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeImmutable
    {
        return $this->date_created;
    }

    public function setDateCreated(\DateTimeImmutable $date_created): self
    {
        $this->date_created = $date_created;

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
    }
}
