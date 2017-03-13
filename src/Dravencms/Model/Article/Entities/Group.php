<?php
namespace Dravencms\Model\Article\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;

/**
 * Class Group
 * @package App\Model\Article\Entities
 * @ORM\Entity
 * @ORM\Table(name="articleGroup")
 */
class Group extends Nette\Object
{
    const SORT_BY_POSITION = 'position';
    const SORT_BY_CREATED_AT = 'createdAt';

    /** @var array */
    public static $sortByList = [
        self::SORT_BY_POSITION => 'Sort by position',
        self::SORT_BY_CREATED_AT => 'Sort by created at',
    ];

    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false,unique=true)
     */
    private $identifier;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isShowName;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $sortBy;

    /**
     * @var ArrayCollection|Article[]
     * @ORM\OneToMany(targetEntity="Article", mappedBy="group",cascade={"persist"})
     */
    private $articles;

    /**
     * @var ArrayCollection|GroupTranslation[]
     * @ORM\OneToMany(targetEntity="GroupTranslation", mappedBy="group",cascade={"persist", "remove"})
     */
    private $translations;

    /**
     * Group constructor.
     * @param $identifier
     * @param $isShowName
     * @param string $sortBy
     */
    public function __construct($identifier, $isShowName, $sortBy = self::SORT_BY_POSITION)
    {
        $this->identifier = $identifier;
        $this->isShowName = $isShowName;
        $this->sortBy = $sortBy;
        $this->articles = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @param boolean $isShowName
     */
    public function setIsShowName($isShowName)
    {
        $this->isShowName = $isShowName;
    }

    /**
     * @param string $sortBy
     */
    public function setSortBy($sortBy)
    {
        if (!array_key_exists($sortBy, self::$sortByList))
        {
            throw new \InvalidArgumentException('$sortBy have wrong value');
        }
        $this->sortBy = $sortBy;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return boolean
     */
    public function isShowName()
    {
        return $this->isShowName;
    }

    /**
     * @return Article[]|ArrayCollection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @return string
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @return ArrayCollection|GroupTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}

