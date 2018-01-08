<?php
/**
 * Created by PhpStorm.
 * User: Christoph
 * Date: 03.01.2018
 * Time: 23:41
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="sitemap_settings")
 */
class SitemapSettings
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", length=16777215, nullable=true)
     */
    private $notIncludedPath;

    /**
     * @ORM\Column(type="text", length=16777215, nullable=true)
     */
    private $notablyPath;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active = true;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Web", inversedBy="sitemapSettings")
     */
    private $web;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getNotIncludedPath()
    {
        return $this->notIncludedPath;
    }

    /**
     * @param mixed $notIncludedPath
     */
    public function setNotIncludedPath($notIncludedPath)
    {
        $this->notIncludedPath = $notIncludedPath;
    }

    /**
     * @return mixed
     */
    public function getNotablyPath()
    {
        return $this->notablyPath;
    }

    /**
     * @param mixed $notablyPath
     */
    public function setNotablyPath($notablyPath)
    {
        $this->notablyPath = $notablyPath;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return ArrayCollection|Web[]
     */
    public function getWeb()
    {
        return $this->web;
    }

    /**
     * @param Web $web
     */
    public function setWeb(Web $web)
    {
        $this->web = $web;
    }

}