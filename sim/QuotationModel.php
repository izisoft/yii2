<?php

namespace izi\sim;

use Yii;

/**
 * This is the model class for table "discount_conditions".
 *
 * @property int $id
 * @property string $min_price
 * @property string $max_price
 * @property string $profit_value
 * @property int $min_value
 * @property int $max_value
 * @property string $condition1
 * @property string $condition2
 * @property string $condition3
 * @property int $sid
 * @property string $group_name
 * @property int $partner_id
 */
class QuotationModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'discount_conditions';
    }

    public static function tableSupplierQuotation()
    {
        return 'simonline_quotations';
    }
    
    public static function tableToGroup()
    {
        return 'simonline_quotation_to_group';
    }
    
    public static function tableToDomain()
    {
        return 'simonline_quotation_to_domain';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['min_price', 'max_price', 'min_value', 'max_value', 'sid', 'partner_id'], 'integer'],
            [['profit_value'], 'number'],
            [['condition1', 'condition2', 'condition3', 'group_name'], 'required'],
            [['condition1', 'condition2', 'condition3', 'group_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'min_price' => 'Min Price',
            'max_price' => 'Max Price',
            'profit_value' => 'Profit Value',
            'min_value' => 'Min Value',
            'max_value' => 'Max Value',
            'condition1' => 'Condition1',
            'condition2' => 'Condition2',
            'condition3' => 'Condition3',
            'sid' => 'Sid',
            'group_name' => 'Group Name',
            'partner_id' => 'Partner ID',
        ];
    }
    
    
    public function getItem($id)
    {
        return QuotationModel::find()->where(['id' => $id])->asArray()->one();
    }
    
    
    public function getItemCondition($id)
    {
        return QuotationModel::find()->from(QuotationModel::tableSupplierQuotation())->where(['id' => $id])->asArray()->one();
    }
    
    public function getAllQuotation()
    {
        return QuotationModel::find()->from(QuotationModel::tableSupplierQuotation())->where(['is_active' => 1])->orderBy(['name' => SORT_ASC])->asArray()->all();
    }
    
    
    private $_discount = [] ;
    public function getConditionsByQuotation($quotation_id, $params = [])
    {
        $scache = md5(json_encode(["quotation_$quotation_id", $params]));
        
        if(isset($this->_discount[$scache])){
            return $this->_discount[$scache];
        }
        
        $query = (new \yii\db\Query())->from(SimDiscountConditions::tableName())->where(['sid' => __SID__, 'quotation_id'=>$quotation_id]);
        
        if(isset($params['price']) && $params['price']>0){
            $query->andWhere(['<=', 'min_price', $params['price']]);
            
            $query->andWhere(['or' ,['>=', 'max_price', $params['price']], ['max_price' => 0]]);
        }
        
        
        $this->_discount[$scache] = $query->orderBy(['min_price'=>SORT_ASC,'max_price'=>SORT_ASC])->all();
        
        return $this->_discount[$scache];
    }
    
    /**
     * Tìm khoảng giá theo mã báo giá
     */
    public function getConditionsByQuotationCode($quotation_code, $params = [])
    {
        $scache = md5(json_encode(["quotation_$quotation_code", $params]));
        
        if(isset($this->_discount[$scache])){
            return $this->_discount[$scache];
        }
        
        $query = (new \yii\db\Query())
        ->select(['a.*'])
        ->from(['a' => SimDiscountConditions::tableName()])
        ->innerJoin(['b' => QuotationModel::tableSupplierQuotation()], 'b.id=a.quotation_id')
        ->where(['a.sid' => __SID__, 'b.code'=>$quotation_code]);
        
        if(isset($params['price']) && $params['price']>0){
            $query->andWhere(['<=', 'a.min_price', $params['price']]);
            
            $query->andWhere(['or' ,['>=', 'a.max_price', $params['price']], ['a.max_price' => 0]]);
        }
        
        
        $this->_discount[$scache] = $query->orderBy(['a.min_price'=>SORT_ASC,'max_price'=>SORT_ASC])->all();
        
        return $this->_discount[$scache];
    }
    
    
    public function countConditionsByQuotation($quotation_id, $params = [])
    {
       
        $query = (new \yii\db\Query())->from(SimDiscountConditions::tableName())->where(['sid' => __SID__, 'quotation_id'=>$quotation_id]);
        
        if(isset($params['price']) && $params['price']>0){
            $query->andWhere(['<=', 'min_price', $params['price']]);
            
            $query->andWhere(['or' ,['>=', 'max_price', $params['price']], ['max_price' => 0]]);
        }
        
        
        return $query->count(1);
        
         
    }
    
    
    public function getConditionsByGroupName($group_name, $params = [])
    {
        $scache = md5(json_encode(["$group_name", $params]));
        
        if(isset($this->_discount[$scache])){
            return $this->_discount[$scache];
        }
        
        $query = (new \yii\db\Query())->from(SimDiscountConditions::tableName())->where(['sid' => __SID__, 'group_name'=>$group_name]);
        
        if(isset($params['price']) && $params['price']>0){
            $query->andWhere(['<=', 'min_price', $params['price']]);
            
            $query->andWhere(['or' ,['>=', 'max_price', $params['price']], ['max_price' => 0]]);
        }
        
        
        $this->_discount[$scache] = $query->orderBy(['min_price'=>SORT_ASC,'max_price'=>SORT_ASC])->all();
        
        return $this->_discount[$scache];
    }
    
    /**
     * set báo giá theo Domain
     */
    public function setDomains($quotation_id, $domains)
    {
        Yii::$app->db->createCommand()->delete(QuotationModel::tableToDomain(), ['quotation_id' => $quotation_id])->execute();
        
        if(!empty($domains)){
            foreach($domains as $domain){
                Yii::$app->db->createCommand()->insert(QuotationModel::tableToDomain(), ['quotation_id' => $quotation_id, 'domain' => $domain])->execute();
            }
        }
        
    }
    
    /**
     * Lấy danh sách domain đc set cho 1 báo giá
     */
    public function getDomains($quotation_id, $params = [])
    {
        
        $query = static::find()
        ->from(['a' => QuotationModel::tableToDomain()]) 
        ->where(['a.quotation_id' => $quotation_id])
      
        ;
        
        $l = $query->asArray()->all();
        
        if(isset($params['distinct']) && !empty($l))
        {
            $d = [];
            foreach ($l as $v){
                $d[] = $v[$params['distinct']];
            }
            return $d;
        }
        
        return $l;
    }
    
    
    public function setGroups($quotation_id, $groups)
    {
        Yii::$app->db->createCommand()->delete(QuotationModel::tableToGroup(), ['quotation_id' => $quotation_id])->execute();
        
        if(!empty($groups)){
            foreach($groups as $group_id){
                Yii::$app->db->createCommand()->insert(QuotationModel::tableToGroup(), ['quotation_id' => $quotation_id, 'group_id' => $group_id])->execute();
            }
        }
        
    }
    
    
    public function getGroups($quotation_id)
    {
        
        $query = static::find()
        ->from(['a' => \izi\models\Customer::tableGroup()])
        ->innerJoin(['b' => QuotationModel::tableToGroup()], 'a.id=b.group_id')
        ->where(['b.quotation_id' => $quotation_id])
        ->orderBy(['a.plevel' => SORT_ASC])
        ;
        
        return $query->asArray()->all();
    }
    
    private $_cache;
    
    /**
     * Lấy báo giá theo domain
     */
    
    public function getQuotationByDomain($domain)
    {
        $www = substr($domain, 0,4);
        if($www == 'www.'){
            $domain = substr($domain, 4);
        }
        
        $k1 = md5(__METHOD__);
        if(isset($this->_cache[$k1][$domain])){
            return $this->_cache[$k1][$domain];
        }
        
        $query = static::find()->from(['a' => QuotationModel::tableSupplierQuotation()])
        ->innerJoin(['b' => QuotationModel::tableToDomain()], 'a.id=b.quotation_id')        
        ->where(['b.domain' => $domain]);
        
        $rs = ($this->_cache[$k1][$domain] = $query->asArray()->one());
        
        
        
        return $rs;
    }
    
    
    /**
     * Lấy báo giá theo mã 
     */
    
    public function getQuotationByCode($code)
    {
 
        $k1 = md5(__METHOD__);
        if(isset($this->_cache[$k1][$code])){
            return $this->_cache[$k1][$code];
        }
        
        $query = static::find()->from(['a' => QuotationModel::tableSupplierQuotation()]) 
        ->where(['a.code' => $code])
        ;
        
//         view($query->createCommand()->getRawSql());
        
        return ($this->_cache[$k1][$code] = $query->asArray()->one());
    }
    
    
    public function getQuotationByGroup($group_id)
    {
        $k1 = md5(__METHOD__); 
        if(isset($this->_cache[$k1][$group_id])){
            return $this->_cache[$k1][$group_id];
        }
        
        $query = static::find()->from(['a' => QuotationModel::tableSupplierQuotation()])
        ->innerJoin(['b' => QuotationModel::tableToGroup()], 'a.id=b.quotation_id')
        ->innerJoin(['c' => \izi\models\Customer::tableGroup()], 'c.id=b.group_id')
        ->where(['b.group_id' => $group_id])
        ->orderBy(['c.plevel' => SORT_ASC])
        ;
        
//         view($query->createCommand()->getRawSql());
        
        return ($this->_cache[$k1][$group_id] = $query->asArray()->one());
    }
    
    /**
     * Copy báo giá
     */
    
    
    public function cloneQuotation($source, $params = [])
    {
        $item = $this->getItemCondition($source);
        
        if($source > 0 && !empty($item)){
            
            $conditions = $this->getConditionsByQuotation($item['id']);
            
            $newData = $item;
            unset($newData['id']);
            
            $newData['name'] = isset($params['name']) ? $params['name'] : '(Bản sao) ' . $item['name'];
            
            $newData['code'] = isset($params['code']) ? $params['code'] : '(Bản sao) ' . $item['code'];
            
            Yii::$app->db->createCommand()->insert(QuotationModel::tableSupplierQuotation(), $newData)->execute();
            $id = Yii::$app->db->lastInsertID;
            
            if(!empty($conditions)){
                foreach ($conditions as $c){
                    unset($c['id']);
                    $c['group_name'] = 'quotation_' . $id;
                    $c['quotation_id'] = $id;
                    Yii::$app->db->createCommand()->insert(QuotationModel::tableName(), $c)->execute();
                }
            }
            
            return $id;
        }
        
        return -1;
    }
    
    
    /**
     * Copy báo giá
     */
    
    
    public function copyQuotation($source, $dest, $params = [])
    {
        if(!($source > 0 && $dest > 0)){
            return;
        }
        
        $item = $this->getItemCondition($source);
        
        if($source > 0 && !empty($item)){
            
            $conditions = $this->getConditionsByQuotation($item['id']);
            
            $newData = $item;
            unset($newData['id']);
            
//             $newData['name'] = isset($params['name']) ? $params['name'] : '(Bản sao) ' . $item['name'];
            
//             $newData['code'] = isset($params['code']) ? $params['code'] : '(Bản sao) ' . $item['code'];
            
//             Yii::$app->db->createCommand()->insert(QuotationModel::tableSupplierQuotation(), $newData)->execute();
//             $id = Yii::$app->db->lastInsertID;

            //
            
            if(!empty($conditions)){
                
                // Xóa báo giá cũ
                Yii::$app->db->createCommand()->delete(QuotationModel::tableName(), ['quotation_id' => $dest])->execute();
                
                foreach ($conditions as $c){
                    unset($c['id']);
                    $c['group_name'] = 'quotation_' . $dest;
                    $c['quotation_id'] = $dest;
                    Yii::$app->db->createCommand()->insert(QuotationModel::tableName(), $c)->execute();
                }
            }
            
            return $dest;
        }
        
        return -1;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
