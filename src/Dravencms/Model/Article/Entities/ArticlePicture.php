<?php declare(strict_types = 1);
namespace Dravencms\Model\Article\Entities;

use Dravencms\Model\File\Entities\StructureFile;
use Dravencms\Model\File\Entities\StructureFileLink;
use Dravencms\Model\User\Entities\User;
use Dravencms\Model\Tag\Entities\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Nette;

/**
 * Class ArticlePicture
 * @package Dravencms\Model\Article\Entities
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="articleArticlePicture")
 */
class ArticlePicture
{
    use Nette\SmartObject;
    use Identifier;
    use TimestampableEntity;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isPrimary;

    /**
     * @var integer
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @var StructureFileLink
     * @ORM\ManyToOne(targetEntity="\Dravencms\Model\File\Entities\StructureFileLink")
     * @ORM\JoinColumn(name="structure_file_link_id", referencedColumnName="id")
     */
    private $structureFileLink;

    /**
     * @var Article
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="pictures")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    private $article;

    /**
     * ArticlePicture constructor.
     * @param Article $article
     * @param StructureFileLink|null $structureFileLink
     * @param bool $isActive
     * @param bool $isPrimary
     */
    public function __construct(
        Article $article,
        StructureFileLink $structureFileLink,
        bool $isActive = true, 
        bool $isPrimary = true
        )
    {
        $this->article = $article;
        $this->isActive = $isActive;
        $this->isPrimary = $isPrimary;
        $this->structureFileLink = $structureFileLink;
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @param boolean $isPrimary
     */
    public function setIsPrimary(bool $isPrimary): void
    {
        $this->isPrimary = $isPrimary;
    }

    /**
     * @param mixed $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @param StructureFileLink|null $structureFileLink
     */
    public function setStructureFileLink(StructureFileLink $structureFileLink = null): void
    {
        $this->structureFileLink = $structureFileLink;
    }

    /**
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return boolean
     */
    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    /**
     * @return mixed
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return StructureFileLink|null
     */
    public function getStructureFileLink(): ?StructureFileLink
    {
        return $this->structureFileLink;
    }

    /**
     * @return Article
     */
    public function getArticle(): Article
    {
        return $this->article;
    }

    /**
     * @param Article $article
     */
    public function setArticle(Article $article): void
    {
        $this->article = $article;
    }
}

