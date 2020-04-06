<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="Pages")
 * @ORM\Entity(repositoryClass="App\Repository\PageRepository")
 */
class Page
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
     * @ORM\Column(name="ID", type="string", length=255)
     * @ORM\Id
     */
    private $uuid;

    /**
     * @ORM\Column(name="French", type="text")
     */
    private $french;

    /**
     * @ORM\Column(name="English", type="text", nullable=true)
     */
    private $english;

    /**
     * @var string
     *
     * @ORM\Column(name="Title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="File", type="string", length=255)
     */
    private $file;

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

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }
}
