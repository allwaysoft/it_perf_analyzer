<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 10/3/11
 * Time: 2:54 AM
 * To change this template use File | Settings | File Templates.
 */
 
class content_description {
    private $description;
    private $language_id;
    private $name;
    private $meta_descriptions;
    private $meta_keywors;
    private $url;

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setLanguageId($language_id)
    {
        $this->language_id = $language_id;
    }

    public function getLanguageId()
    {
        return $this->language_id;
    }

    public function setMetaDescriptions($meta_descriptions)
    {
        $this->meta_descriptions = $meta_descriptions;
    }

    public function getMetaDescriptions()
    {
        return $this->meta_descriptions;
    }

    public function setMetaKeywors($meta_keywors)
    {
        $this->meta_keywors = $meta_keywors;
    }

    public function getMetaKeywors()
    {
        return $this->meta_keywors;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }
}
