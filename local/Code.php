<?php 
namespace izi\local;
use Yii;
class Code extends \yii\base\Component
{
    public $local_id;
    
    public $listCode = [];
     
    
    private $_field  ;
    
    private $_model;
    
    public function setLocalId($local_id){
        $this->local_id = $local_id;
    }
    
    public function clear(){
        $this->_iso2 = $this->_iso3 = null;
    }
    
    
    public function getField(){
        if($this->_field == null){
            $this->_field = $this->setField();           
        }
        return $this->_field;
    }
    public function setField(){
        $obj = new \stdClass();
        $obj->iso1 = 'ISO-3166-1';
        $obj->iso2 = 'ISO-3166-1-A2';
        $obj->iso3 = 'ISO-3166-1-A3';
        $obj->domain = 'Domain';
        $obj->e164 = 'E164';
        $obj->e212 = 'E212';
        
        $obj->fips = 'FIPS';
        $obj->gs1 = 'GTIN-GS1';
        $obj->aircraft = 'ICAO-aircraft';
        $obj->airport = 'ICAO-airport';
        $obj->ioc = 'IOC';
        $obj->callsign = 'ITU-callsign';
        $obj->letter = 'ITU-Letter';
        $obj->maritime = 'ITU-Maritime';
        $obj->license = 'License-plate';
        $obj->marc = 'LOC-MARC';
        $obj->nato2 = 'NATO2';
        $obj->nato3 = 'NATO3';
        $obj->undp = 'UNDP';
        $obj->wmo = 'WMO';
        
        
        return $obj;
    }
    
    public function getModel(){
        if($this->_model == null){
            $this->_model = Yii::createObject('izi\local\models\Code'); 
        }
        return $this->_model;
    }
    
    public function findCountriesCode($country_id){
        if(!isset($this->listCode[$country_id])){
            $this->listCode[$country_id] = $this->getModel()->findCountriesCode($country_id);
        }
        return $this->listCode[$country_id];
    }
    
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, 'set' . $name)) {
            throw new \yii\base\InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        }
        
        $keys = array_keys( get_object_vars($this->getField()));
        if(in_array($name, $keys)){
            $code = $this->findCountriesCode($this->local_id);
            return isset($code[$this->getField()->$name]) ? $code[$this->getField()->$name] : null;    
        }
        throw new \yii\base\UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
    }
    
    
     
    
    
    private $_iso2;
    public function getIso2(){        
        
        if($this->_iso2 == null){          
            $code = $this->findCountriesCode($this->local_id);
            $this->_iso2 = isset($code[$this->getField()->iso2]) ? $code[$this->getField()->iso2] : null;            
        }
     
        return $this->_iso2;
    }
    
    private $_iso3;
    public function getIso3(){
        if($this->_iso3 == null){
            $code = $this->findCountriesCode($this->local_id);
            $this->_iso3 = isset($code[$this->getField()->iso3]) ? $code[$this->getField()->iso3] : null;
        }
        return $this->_iso3;
    }
}