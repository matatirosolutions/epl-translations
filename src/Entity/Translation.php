<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="Strings")
 * @ORM\Entity(repositoryClass="App\Repository\TranslationRepository")
 */
class Translation
{
    /**
     * @var int
     *
     * @ORM\Column(name="rec_id", type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="PrimaryKey", type="string", length=255)
     * @ORM\Id
     * @ORM\GeneratedValue()
     */
    private $uuid;

    /**
     * @var string
     *
     * @ORM\Column(name="ID", type="string", length=255)
     */
    private $stringId;

    /**
     * @ORM\Column(name="French", type="text")
     */
    private $french;

    /**
     * @ORM\Column(name="English", type="text", nullable=true)
     */
    private $english;

    /**
     * @ORM\Column(name="Swedish", type="text", nullable=true)
     */
    private $swedish;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getFrench(): ?string
    {
        return $this->french;
    }

    public function setFrench(string $french): self
    {
        $this->french = $french;

        return $this;
    }

    public function getEnglish(): ?string
    {
        return $this->english;
    }

    public function setEnglish(?string $english): self
    {
        $this->english = $english;

        return $this;
    }

    public function getSwedish(): ?string
    {
        return $this->swedish;
    }

    public function setSwedish(?string $swedish): self
    {
        $this->swedish = $swedish;

        return $this;
    }

    public function getStringId(): string
    {
        return $this->stringId;
    }

    public function setStringId(string $stringId): self
    {
        $this->stringId = $stringId;

        return $this;
    }
}
