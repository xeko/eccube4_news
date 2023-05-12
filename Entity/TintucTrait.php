<?php

namespace Plugin\NewsUpgrade\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;

/**
 * @Eccube\EntityExtension("Eccube\Entity\News")
 */
trait TintucTrait {

    /**
     * @var string
     *
     * @ORM\Column(name="tt_thumbnail_url", type="string", nullable=true)
     */
    private $tt_thumbnail_url;

    /**
     * @var string
     *
     * @ORM\Column(name="ttseo_title", type="string", nullable=true)
     */
    private $ttseo_title;

    /**
     * @var string
     *
     * @ORM\Column(name="ttseo_description", type="string", nullable=true)
     */
    private $ttseo_description;

    /**
     * @var string
     *
     * @ORM\Column(name="ttseo_robots", type="string", nullable=true , options={"default" : "index,follow"})
     */
    private $ttseo_robots;

    /**
     * @return string
     */
    public function getTtThumbnailUrl() {
        return $this->tt_thumbnail_url;
    }

    /**
     * @param string $tt_thumbnail_url
     */
    public function setTtThumbnailUrl($tt_thumbnail_url) {
        $this->tt_thumbnail_url = $tt_thumbnail_url;
    }

    /**
     * @return string
     */
    public function getTtseoTitle() {
        return $this->ttseo_title;
    }

    /**
     * @param string $ttseo_title
     */
    public function setTtseoTitle($ttseo_title) {
        $this->ttseo_title = $ttseo_title;
    }

    /**
     * @return string
     */
    public function getTtseoDescription() {
        return $this->ttseo_description;
    }

    /**
     * @param string $ttseo_description
     */
    public function setTtseoDescription($ttseo_description) {
        $this->ttseo_description = $ttseo_description;
    }

    /**
     * @return boolean
     */
    public function getTtseoRobots() {
        return $this->ttseo_robots;
    }

    /**
     * @param boolean $ttseo_robots
     */
    public function setTtseoRobots($ttseo_robots) {
        $this->ttseo_robots = $ttseo_robots;
    }

}
