<?php
    
namespace CoreShop;

use CoreShop\Base;

use Pimcore\Model\Object;
use Pimcore\Model\Asset\Image;

class Category extends Base {
    
    public static function getAll()
    {
        $list = new Object\CoreShopCategory\Listing();
        
        return $list->getObjects();
    }

    public static function getFirstLevel()
    {
        $list = new Object\CoreShopCategory\Listing();
        $list->setCondition("parentCategory__id is null");

        return $list->getObjects();
    }
    
    public function getProducts()
    {
        $list = new Object\CoreShopProduct\Listing();
        $list->setCondition("enabled = 1 AND categories LIKE '%,".$this->getId().",%'");

        return $list->getObjects();
    }

    public function getProductsPaging($page = 0, $itemsPerPage = 10, $sort = array("name" => "name", "direction" => "asc"))
    {
        $list = new Object\CoreShopProduct\Listing();
        $list->setCondition("enabled = 1 AND categories LIKE '%,".$this->getId().",%'");

        $list->setOrderKey($sort['name']);
        $list->setOrder($sort['direction']);

        $paginator = \Zend_Paginator::factory($list);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($itemsPerPage);

        return $paginator;
    }

    public function getDefaultImage()
    {
        $config = Config::getConfig();
        $config = $config->toArray();
        $image = Image::getByPath($config['category']['default-image']);

        if($image instanceof Image)
            return $image;

        return false;
    }

    public function getImage()
    {
        if($this->getCategoryImage() instanceof Image)
        {
            return $this->getCategoryImage();
        }

        return $this->getDefaultImage();
    }

    public function getHierarchy()
    {
        $hierarchy = array();

        $category = $this;

        do {
            $hierarchy[] = $category;

            $category = $category->getParentCategory();
        }
        while($category instanceof Object\CoreShopCategory);

        return array_reverse($hierarchy);
    }

    public function getChildCategories()
    {
        $list = new Object\CoreShopCategory\Listing();
        $list->setCondition("parentCategory__id = ?", array($this->getId()));

        return $list->getObjects();
    }
}