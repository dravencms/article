<?php
namespace Dravencms\Model\Article\Entities;

use Dravencms\Model\Locale\Entities\Locale;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;

/**
 * Class ArticleTranslation
 * @package Dravencms\Model\Article\Entities
 * @ORM\Entity
 * @ORM\Table(name="articleArticleTranslation")
 */
class ArticleTranslation extends Nette\Object
{
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $subtitle;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $lead;

    /**
     * @var string
     * @ORM\Column(type="text",nullable=false)
     */
    private $text;

    /**
     * @var string
     * @ORM\Column(type="text",nullable=true)
     */
    private $perex;

    /**
     * @var Article
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="translations")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    private $article;
    /**
     * @var Locale
     * @ORM\ManyToOne(targetEntity="Dravencms\Model\Locale\Entities\Locale")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id")
     */
    private $locale;

    /**
     * ArticleTranslation constructor.
     * @param string $name
     * @param string $subtitle
     * @param string $lead
     * @param string $text
     * @param string $perex
     * @param Article $article
     * @param Locale $locale
     */
    public function __construct(Article $article, Locale $locale, $name, $subtitle, $lead, $text, $perex)
    {
        $this->name = $name;
        $this->subtitle = $subtitle;
        $this->lead = $lead;
        $this->text = $text;
        $this->perex = $perex;
        $this->article = $article;
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
     * @param string $lead
     */
    public function setLead($lead)
    {
        $this->lead = $lead;
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
     * @param Article $article
     */
    public function setArticle(Article $article)
    {
        $this->article = $article;
    }

    /**
     * @param Locale $locale
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;
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
    public function getLead()
    {
        return $this->lead;
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
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }
}

