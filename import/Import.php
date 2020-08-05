<?php
namespace izi\import;
use Yii;
class Import extends \yii\base\Component
{
    
    
    
    private $_qm;
    
    public function getQm(){
        if($this->_qm === null){
            $this->_qm = Yii::createObject([
                'class' => 'izi\import\qm\Qm',
                 
            ]);
        }
        return $this->_qm;
    }
    
    
    
    private $_sim;
    
    public function getSim(){
        if($this->_sim === null){
            $this->_sim = Yii::createObject([
                'class' => 'izi\import\sim\Sim',
                
            ]);
        }
        return $this->_sim;
    }
    
    private $_fplus;
    
    public function getFplus(){
        if($this->_fplus === null){
            $this->_fplus = Yii::createObject([
                'class' => 'izi\import\fplus\Product',
                
            ]);
        }
        return $this->_fplus;
    }
    
    
    private $_kenh14;
    
    public function getKenh14(){
        if($this->_kenh14 === null){
            $this->_kenh14 = Yii::createObject([
                'class' => 'izi\import\kenh14\Kenh14',
                
            ]);
        }
        return $this->_kenh14;
    }
    
    private $_sinhcafe;
    
    public function getSinhcafe(){
        if($this->_sinhcafe === null){
            $this->_sinhcafe = Yii::createObject([
                'class' => 'izi\import\sinhcafe\Product',
                
            ]);
        }
        return $this->_sinhcafe;
    }
    
    
    private $_tomorow;
    
    public function getTomorow(){
        if($this->_tomorow === null){
            $this->_tomorow = Yii::createObject([
                'class' => 'izi\import\tomorow\Product',
                
            ]);
        }
        return $this->_tomorow;
    }
    
    
    private $_masoffer;
    
    public function getMasoffer(){
        if($this->_masoffer === null){
            $this->_masoffer = Yii::createObject([
                'class' => 'izi\import\masoffer\MasOffer',
                
            ]);
        }
        return $this->_masoffer;
    }
    
    private $_accessTrade;
    
    public function getAccessTrade(){
        if($this->_accessTrade === null){
            $this->_accessTrade = Yii::createObject([
                'class' => 'izi\import\accesstrade\AccessTrade',
                
            ]);
        }
        return $this->_accessTrade;
    }
    
    
    
    private $_crawler;
    
    public function getCrawler(){
        if($this->_crawler === null){
            $this->_crawler = Yii::createObject([
                'class' => 'izi\import\crawler\Crawler',
                
            ]);
        }
        return $this->_crawler;
    }
    
}