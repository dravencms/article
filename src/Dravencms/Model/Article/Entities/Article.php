<?php declare(strict_types = 1);
namespace Dravencms\Model\Article\Entities;

use Dravencms\Model\File\Entities\StructureFile;
use Dravencms\Model\Tag\Entities\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Nette;

/**
 * Class Article
 * @package Dravencms\Model\Article\Entities
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="articleArticle", uniqueConstraints={@UniqueConstraint(name="identifier_unique", columns={"identifier", "group_id"})})
 */
class Article
{
    use Nette\SmartObject;
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $identifier;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isShowName;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isAutoDetectTags;

    /**
     * @var integer
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @var StructureFile
     * @ORM\ManyToOne(targetEntity="\Dravencms\Model\File\Entities\StructureFile")
     * @ORM\JoinColumn(name="structure_file_id", referencedColumnName="id")
     */
    private $structureFile;

    /**
     * @var \Doctrine\Common\Collections\Collection|Tag[]
     *
     * @ORM\ManyToMany(targetEntity="\Dravencms\Model\Tag\Entities\Tag")
     * @ORM\JoinTable(
     *  name="article_tag",
     *  joinColumns={
     *      @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     *  },
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     *  }
     * )
     */
    private $tags;

    /**
     * @var Group
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="articles")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    private $group;

    /**
     * @var ArrayCollection|ArticleTranslation[]
     * @ORM\OneToMany(targetEntity="ArticleTranslation", mappedBy="article",cascade={"persist", "remove"})
     */
    private $translations;

    /**
     * Article constructor.
     * @param Group $group
     * @param $identifier
     * @param bool $isActive
     * @param bool $isShowTitle
     * @param bool $isAutoDetectTags
     * @param StructureFile|null $file
     */
    public function __construct(Group $group, string $identifier, bool $isActive = true, bool $isShowTitle = true, bool $isAutoDetectTags = true, StructureFile $file = null)
    {
        $this->group = $group;
        $this->identifier = $identifier;
        $this->isActive = $isActive;
        $this->isShowName = $isShowTitle;
        $this->isAutoDetectTags = $isAutoDetectTags;
        $this->structureFile = $file;

        $this->tags = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @param boolean $isShowName
     */
    public function setIsShowName(bool $isShowName): void
    {
        $this->isShowName = $isShowName;
    }

    /**
     * @param boolean $isAutoDetectTags
     */
    public function setIsAutoDetectTags(bool $isAutoDetectTags): void
    {
        $this->isAutoDetectTags = $isAutoDetectTags;
    }

    /**
     * @param mixed $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @param StructureFile|null $structureFile
     */
    public function setStructureFile(StructureFile $structureFile = null): void
    {
        $this->structureFile = $structureFile;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @param Tag $tag
     */
    public function addTag(Tag $tag): void
    {
        if ($this->tags->contains($tag))
        {
            return;
        }
        $this->tags->add($tag);
    }

    /**
     * @param Tag $tag
     */
    public function removeTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag))
        {
            return;
        }
        $this->tags->removeElement($tag);
    }

    /**
     *
     * @param ArrayCollection $tags
     */
    public function setTags(ArrayCollection $tags): void
    {
        //Remove all not in
        foreach($this->tags AS $tag)
        {
            if (!$tags->contains($tag))
            {
                $this->addTag($tag);
            }
        }

        //Add all new
        foreach($tags AS $tag)
        {
            if (!$this->tags->contains($tag))
            {
                $this->removeTag($tag);
            }
        }
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
    public function isShowName(): bool
    {
        return $this->isShowName;
    }

    /**
     * @return boolean
     */
    public function isAutoDetectTags(): bool
    {
        return $this->isAutoDetectTags;
    }

    /**
     * @return string
     */
    public function getLead(): string
    {
        return $this->lead;
    }

    /**
     * @return mixed
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return StructureFile
     */
    public function getStructureFile(): ?StructureFile
    {
        return $this->structureFile;
    }

    /**
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection|\Dravencms\Model\Tag\Entities\Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @return Group
     */
    public function getGroup(): Group
    {
        return $this->group;
    }

    /**
     * @param Group $group
     */
    public function setGroup(Group $group): void
    {
        $this->group = $group;
    }

    /**
     * @return ArrayCollection|ArticleTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}

