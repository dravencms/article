<?php
namespace Dravencms\Model\Article\Entities;

use Dravencms\Model\File\Entities\StructureFile;
use Dravencms\Model\Tag\Entities\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Gedmo\Sortable\Sortable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;

/**
 * Class Article
 * @package Dravencms\Model\Article\Entities
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="articleArticle", uniqueConstraints={@UniqueConstraint(name="name_unique", columns={"name", "group_id"})})
 */
class Article extends Nette\Object
{
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $name;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $subtitle;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $lead;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="text",nullable=false)
     */
    private $text;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="text",nullable=true)
     */
    private $perex;

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
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     * and it is not necessary because globally locale can be set in listener
     */
    private $locale;

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
     * Article constructor.
     * @param Group $group
     * @param $name
     * @param $text
     * @param null $subtitle
     * @param null $lead
     * @param null $perex
     * @param bool $isActive
     * @param bool $isShowTitle
     * @param bool $isAutoDetectTags
     * @param StructureFile|null $file
     */
    public function __construct(Group $group, $name, $text, $subtitle = null, $lead = null, $perex = null, $isActive = true, $isShowTitle = true, $isAutoDetectTags = true, StructureFile $file = null)
    {
        $this->group = $group;
        $this->name = $name;
        $this->subtitle = $subtitle;
        $this->text = $text;
        $this->perex = $perex;
        $this->isActive = $isActive;
        $this->isShowName = $isShowTitle;
        $this->isAutoDetectTags = $isAutoDetectTags;
        $this->lead = $lead;
        $this->structureFile = $file;

        $this->tags = new ArrayCollection();
    }


    /**
     * @param $locale
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $subtitle
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @param string $perex
     */
    public function setPerex($perex)
    {
        $this->perex = $perex;
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
     * @param string $lead
     */
    public function setLead($lead)
    {
        $this->lead = $lead;
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getPerex()
    {
        return $this->perex;
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
     * @return \App\Model\Tag\Entities\Tag[]|\Doctrine\Common\Collections\Collection
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
}

