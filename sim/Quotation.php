<?php

namespace izi\sim;

use Yii;

class Quotation extends \yii\base\Component
{
    
    private $_model;
    
    public function getModel(){
        if($this->_model == null){
            $this->_model = Yii::createObject([
                'class' =>  'izi\\sim\\QuotationModel',
                
            ]);
        }
        
        return $this->_model;
    }
    
    
    private $_quotationId;
    
    public function getQuotationId()
    {
        if($this->_quotationId == null)
        {
            $c = $this->getModel()->getQuotationByDomain(DOMAIN);
            if(empty($c)){
                $c = $this->getModel()->getQuotationByCode('web_default');
            }
            
            if(!empty($c)){
                $this->_quotationId = $c['id'];
            }
        }
        
        return $this->_quotationId;
    }
}
