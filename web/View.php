<?php 
namespace izi\web;

class View extends \yii\web\View

{
    
    public $specialPage = ['amp','api'];
    
    /**
     * set view category
     * 
     */
    private $_category;
    
    public function getCategory()
    { 
        return $this->_category;   
    }
    
    public function setCategory($value)
    {
        $this->_category = $value;
    }
    
    /**
     * set view item
     *
     */
    private $_item;
    
    public function getItem()
    {
        return $this->_item;
    }
    
    public function setItem($value)
    {
        $this->_item = $value;
    }
    
    /**
     * set view item
     *
     */
    private $_template;
    
    public function getTemplate()
    {
        return $this->_template;
    }
    
    public function setTemplate($value)
    {
        $this->_template = $value;
    }
    
    private $_config;
    
    
    public function getConfig()
    {
        if($this->_config == null){
            $this->_config = (object)\app\models\SiteConfigs::getConfigs('SITE_CONFIGS', __LANG__ , __SID__, true, true);
        }
        return $this->_config;
    }
    
    
    public function setSiteConfig($key, $value){
        
        $keys = explode('|', $key);
        
        switch (count($keys)){
            case 0:
                return;
                break;
            case 1:
                
                $this->getConfig()->{$keys[0]} = $value;
                
                break;
            case 2:
                if(isset($this->getConfig()->{$keys[0]}{$keys[1]}) && is_array($this->getConfig()->{$keys[0]}{$keys[1]})){
                    $this->getConfig(){$keys[0]}{$keys[1]} = $value;
                }else{
                    $this->getConfig()->{$keys[0]}->{$keys[1]} = $value;
                }
                break;
                
        }
        
    }
    
    
    
}
