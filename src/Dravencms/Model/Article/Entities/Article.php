<?php
namespace Dravencms\Model\Article\Entities;

use Dravencms\Model\File\Entities\StructureFile;
use Dravencms\Model\Tag\Entities\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
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
    public function __construct(Group $group, $identifier, $isActive = true, $isShowTitle = true, $isAutoDetectTags = true, StructureFile $file = null)
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
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @param boolean $isShowName
     */
    public function setIsShowName($isShowName)
    {
        $this->isShowName = $isShowName;
    }

    /**
     * @param boolean $isAutoDetectTags
     */
    public function setIsAutoDetectTags($isAutoDetectTags)
    {
        $this->isAutoDetectTags = $isAutoDetectTags;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @param StructureFile|null $structureFile
     */
    public function setStructureFile(StructureFile $structureFile = null)
    {
        $this->structureFile = $structureFile;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @param Tag $tag
     */
    public function addTag(Tag $tag)
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
    public function removeTag(Tag $tag)
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
    public function setTags(ArrayCollection $tags)
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
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @return boolean
     */
    public function isShowName()
    {
        return $this->isShowName;
    }

    /**
     * @return boolean
     */
    public function isAutoDetectTags()
    {
        return $this->isAutoDetectTags;
    }

    /**
     * @return string
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return StructureFile
     */
    public function getStructureFile()
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
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param Group $group
     */
    public function setGroup($group)
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
    public function getIdentifier()
    {
        return $this->identifier;
    }
}

