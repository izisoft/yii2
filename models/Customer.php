<?php
namespace izi\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class Customer extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = -5;
    const STATUS_ACTIVE = 10;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customers}}';
    }

    public static function tableToPlace()
    {
        return '{{%customers_to_places}}';
    }
public static function tableGroup()
    {
        return '{{%customer_groups}}';
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {

        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id , $params = [])
    {
        return static::findOne(['id' => $id, 'sid'=>__SID__]);
    }

    public static function findIdentityByCode($code , $params = [])
    {
        $condition = ['code' => $code, 'sid'=>__SID__];
        
        if(isset($params['type_id'])){
            $condition ['type_id'] = $params['type_id'];
        }
        
        return static::findOne($condition);
    }
    
    /**
     * @inheritdoc 
     */
    public function getItem($id , $params = [])
    {
        
        $condition = ['id' => $id, 'sid'=>__SID__];
        
        if(isset($params['type_id'])){
            $condition ['type_id'] = $params['type_id'];
        }
        
        $query = static::find()->where($condition);
        
        if(isset($params['select'])){
            $query->addSelect($params['select']); 
        }
        
        if(isset($params['select_option'])){
            switch ($params['select_option']){
                case 'mini':
                    $query->addSelect([
                        'name',
                        'lang_code',
                        'short_name',
//                         'address',
//                         'phone',
//                         'local_id',
                        'bizrule',
                        
                        
                    ]);
                    break;
            }
            
        }
        
        return $query->asArray()->one();
    }
    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        //return $this->type;
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function getContacts($supplier_id , $params = []){
        
        $order_by = isset($params['order_by']) ? $params['order_by'] : ['a.name'=>SORT_ASC];
        
        $query = static::find()
        ->select(['a.*'])
        ->from(['a'=>Customer::tableName()]);
        
        $query->where(['a.sid'=>__SID__,'a.type_id'=>TYPE_ID_CONTACT]);
        
        $query->andWhere(['>','a.state',-2]);
        
        $query->innerJoin(['b'=>'contacts'],'a.id=b.contact_id')->andWhere(['b.supplier_id'=>$supplier_id]);
        //view($query->createCommand()->getRawSql());
        return $query->orderBy($order_by)->asArray()->all();
    }
    
    
    public function getContact($id, $supplier_id = 0 , $params = []){        
        
        $query = static::find()
        ->select(['a.*'])
        ->from(['a'=>Customer::tableName()]);
        
        $query->where(['a.sid'=>__SID__,'a.type_id'=>TYPE_ID_CONTACT]);
        
        $query->andWhere(['>','a.state',-2]);     
        
        if(validateEmail($id)){ 
            $query->andWhere(['a.email' => $id]);     
        }else{
            $query->andWhere(['a.id' => $id]); 
        }
        if($supplier_id>0){
            $query->innerJoin(['b'=>'contacts'],'a.id=b.contact_id')->andWhere(['b.supplier_id'=>$supplier_id]);
        }
        return $query->one();
    }
    
    public function findCustomer($params){
        $guide_attrs = isset($params['guide_attrs']) && $params['guide_attrs'] == true ? true  : false;
        $parent_id = isset($params['parent_id']) ? $params['parent_id'] : 0;
        $filter_text = isset($params['filter_text']) ? $params['filter_text'] : '*';
        
        $is_active = isset($params['is_active']) ? $params['is_active'] : -1;
        
        $order_by = isset($params['order_by']) ? $params['order_by'] : ['name'=>SORT_ASC];
        
        $query = static::find()
        ->select(['a.*'])
        ->from(['a'=>Customer::tableName()]);
        
        $query->where(['a.sid'=>__SID__,'a.type_id'=>$params['type_id']]);
        
        $query->andWhere(['>','a.state',-2]);
        
        if($guide_attrs){
            $query->innerJoin(['g'=>'guide_attrs'],'a.id=g.guide_id');
            $query->addSelect(['g.*']);
            
            
            if($parent_id != -1){
                
                $query->andWhere(['g.guide_id'=>(new \yii\db\Query())->from('guide_to_group')->where(['group_id'=>$parent_id])->select('guide_id')]);
            }else{
                $query->andWhere(['g.parent_id'=>$parent_id]);
            }
            
            $language = isset($params['language']) ? $params['language'] : false;
            if($language !== false){
                $query->andFilterWhere(['like', 'g.language',$language]);
            }
        }
        if($filter_text != '*'){
            $query->andFilterWhere(['or',[
                'like','a.name',$filter_text
            ],
                [
                    'like','a.code',$filter_text
                ],
                [
                    'like','a.phone',$filter_text
                ],
                [
                    'like','a.email',$filter_text
                ]
                
            ]);
        }
        
        if($is_active != -1){
            $query->andWhere(['a.is_active'=>$is_active]);
        }
        
        if(isset($params['not in'])){
            $query->andWhere(['not in', 'a.id' , $params['not in']]);
        }
        
        if(isset($params['not_in'])){
            $query->andWhere(['not in', 'a.id' , $params['not_in']]);
        }
        
        if(isset($params['in'])){
            $query->andWhere(['in', 'a.id' , $params['in']]);
        }
        
        
        if(isset($params['rating']) && $params['rating'] !== null){
            $query->andWhere(['a.rating' => $params['rating']]);
        }
        
        $place = isset($params['place']) ? $params['place'] : [];
        
        if(isset($params['place_id']) && !empty($params['place_id'])){
            $place[] = $params['place_id'];
        }
        
        if(!empty($place)){
            $query->innerJoin(['p'=>Customer::tableToPlace()],'a.id=p.customer_id')
            ->andWhere(['p.place_id'=>$place]);
        }
        
        
        
        return $query->orderBy($order_by)->asArray()->all();
    }
    
    
    
    public function findCustomerByEmail($email, $params){
        $guide_attrs = isset($params['guide_attrs']) && $params['guide_attrs'] == true ? true  : false;
        $parent_id = isset($params['parent_id']) ? $params['parent_id'] : 0;
        $filter_text = isset($params['filter_text']) ? $params['filter_text'] : '*';
        
        $is_active = isset($params['is_active']) ? $params['is_active'] : -1;
        
        $order_by = isset($params['order_by']) ? $params['order_by'] : ['name'=>SORT_ASC];
        
        $query = static::find()
        ->select(['a.*'])
        ->from(['a'=>Customer::tableName()]);
        
        $query->where(['a.sid'=>__SID__,'a.type_id'=>$params['type_id'],'a.email'=>$email]);
        
        $query->andWhere(['>','a.state',-2]);
        
        if($guide_attrs){
            $query->innerJoin(['g'=>'guide_attrs'],'a.id=g.guide_id');
            $query->addSelect(['g.*']);
            if($parent_id>0){
                $query->andWhere(['g.parent_id'=>$parent_id]);
            }
            $language = isset($params['language']) ? $params['language'] : false;
            if($language !== false){
                $query->andFilterWhere(['like', 'g.language',$language]);
            }
        }
        if($filter_text != '*'){
            $query->andFilterWhere(['or',[
                'like','a.name',$filter_text
            ],
                [
                    'like','a.code',$filter_text
                ],
                [
                    'like','a.phone',$filter_text
                ],
                [
                    'like','a.email',$filter_text
                ]
                
            ]);
        }
        
        if($is_active != -1){
            $query->andWhere(['a.is_active'=>$is_active]);
        }
        
        if(isset($params['not in'])){
            $query->andWhere(['not in', 'a.id' , $params['not in']]);
        }
        
        if(isset($params['not_in'])){
            $query->andWhere(['not in', 'a.id' , $params['not_in']]);
        }
        
        if(isset($params['in'])){
            $query->andWhere(['in', 'a.id' , $params['in']]);
        }
        
        if(isset($params['place']) && !empty($params['place'])){
            $query->innerJoin(['p'=>Customer::tableToPlace()],'a.id=p.customer_id')
            ->andWhere(['p.place_id'=>$params['place']]);
        }
        
        
        return $query->orderBy($order_by)->asArray()->one();
    }
    
    
    public function getAll($params = []){
        $guide_attrs = isset($params['guide_attrs']) && $params['guide_attrs'] == true ? true  : false;
        $parent_id = isset($params['parent_id']) ? $params['parent_id'] : 0;
        $filter_text = isset($params['filter_text']) ? $params['filter_text'] : '*';
        
        $order_by = isset($params['order_by']) ? $params['order_by'] : ['name'=>SORT_ASC];
        
        $query = static::find()
        ->select(['a.*'])
        ->from(['a'=>Customer::tableName()]);
        
        $query->where(['a.sid'=>__SID__])->andWhere(['>','a.state',-2]);
        
        if(isset($params['type_id'])){
            $query->andWhere(['a.type_id'=>$params['type_id']]);
        }
        
        if($guide_attrs){
            $query->innerJoin(['g'=>'guide_attrs'],'a.id=g.guide_id');
            $query->addSelect(['g.*']);
            if($parent_id>0){
                $query->andWhere(['g.parent_id'=>$parent_id]);
            }
            $language = isset($params['language']) ? $params['language'] : false;
            if($language !== false){
                $query->andFilterWhere(['like', 'g.language',$language]);
            }
        }
        if($filter_text != '*'){
            $query->andFilterWhere(['or',[
                'like','a.name',$filter_text
            ],
                [
                    'like','a.code',$filter_text
                ],
                [
                    'like','a.phone',$filter_text
                ],
                [
                    'like','a.email',$filter_text
                ]
                
            ]);
        }
        
        if(isset($params['not in'])){
            $query->andWhere(['not in', 'a.id' , $params['not in']]);
        }
        
        if(isset($params['not_in'])){
            $query->andWhere(['not in', 'a.id' , $params['not_in']]);
        }
        
        if(isset($params['in'])){
            $query->andWhere(['in', 'a.id' , $params['in']]);
        }
        
        if(isset($params['place']) && !empty($params['place'])){
            $query->innerJoin(['p'=>Customer::tableToPlace()],'a.id=p.customer_id')
            ->andWhere(['p.place_id'=>$params['place']]);
        }
        
        
        return $query->orderBy($order_by)->asArray()->all();
    }
    
    
    
    public function getPlaceByCustomer($customer_id){
        $query = static::find()->from(['a'=>Place::tableName()])
        ->innerJoin(['b'=>Customer::tableToPlace()],'a.id=b.place_id')
        ->where(['b.customer_id'=>$customer_id]);
        return $query->asArray()->all();
    }
    
    public function getPlaceIdByCustomer($customer_id){
        $l = $this->getPlaceByCustomer($customer_id);
        $rs = [];
        if(!empty($l)){
            foreach ($l as $v){
                $rs[] = $v['id'];
            }
        }
        return $rs;
    }
    
    public function getCustomerInfoWithPlace($customer_id){
        
    }

    
    public function getPackages($supplier_id){
        $query = static::find()->from(['a'=>'package_prices'])->innerJoin(['b'=>'package_to_supplier'],'a.id=b.package_id')
        ->where(['b.supplier_id'=>$supplier_id]);
        return $query->orderBy(['a.title'=>SORT_ASC])->asArray()->all();
    }

    
    public function cloneItem($from_id, $to_id =0){
        if(!($to_id>0)){
            $to_id = Yii::$app->zii->insert($this->tableName(),['sid'=>__SID__]);
        }
        
        $item = Yii::$app->db->createCommand("select * from {$this->tableName()} where id=$from_id")->queryOne(null,false);
        
        $columns = Yii::$app->db->schema->getTableSchema('customers')->getColumnNames();
        ///unset($columns['id']);
        
        $v = [];
        
        foreach ( $columns as $column){
            switch ($column){
                case 'id': break;
                case 'name':
                    
                    $v[] = "dest.$column=concat('(Bản sao)', ' ', src.$column)";
                    break;
                case 'code':
                    
                    $v[] = "dest.$column=concat('COPY_', '', src.$column)";
                    break;
                default:
                    
                    $v[] = "dest.$column=src.$column";
                    break;
            }
            if($column == 'id'){
                
            }else{
             
            }
        }
        
         
        
        $sql = "UPDATE
    `customers` AS `dest`,
    (
        SELECT
            *
        FROM
            `customers`
        WHERE
            `id` = $from_id
    ) AS `src`
SET
    ".(implode(',', $v))."
WHERE
    `dest`.`id` = $to_id
;";
        
        Yii::$app->db->createCommand($sql)->execute();
        Yii::$app->db->createCommand()->update('customers', [
            'created_by'=>Yii::$app->user->id,
            'updated_at'=>time(),
            'created_at'=>time(),
            
        ],['id'=>$to_id])->execute();
        
        // Update data
        $data = $this->getItemData($from_id);
        // Place 
        Yii::$app->db->createCommand()->delete('customers_to_places',['customer_id'=>$to_id])->execute();
        if(!empty($data['place'])){
            foreach ($data['place'] as $place){
                Yii::$app->db->createCommand()->insert('customers_to_places',[
                    'customer_id'=>$to_id,
                    'place_id'=>$place['id'],
                ])->execute();
            }
        }
        // Season
        Yii::$app->db->createCommand()->delete('seasons_categorys_to_suppliers',['supplier_id'=>$to_id])->execute();
        if(!empty($data['season'])){
            foreach ($data['season'] as $v){
                Yii::$app->db->createCommand()->insert('seasons_categorys_to_suppliers',[
                    'supplier_id'=>$to_id,
                    'season_id'=>$v['id'],
                    'parent_id'=>$v['parent_id'],
                    'price_type'=>$v['price_type'],
                    'price_incurred'=>$v['price_incurred'],
                    'currency'=>$v['currency'],
                    'unit_price'=>$v['unit_price'],
                    
                    'sub_id'=>$v['sub_id'],
                    'object_id'=>$v['object_id'],
                    'time_id'=>$v['time_id'],
                    'priority'=>$v['priority'],
                    
                ])->execute();
            }
        }
        
        Yii::$app->db->createCommand()->delete('seasons_to_suppliers',['supplier_id'=>$to_id])->execute();
        if(!empty($data['season_child'])){
            foreach ($data['season_child'] as $v){
                Yii::$app->db->createCommand()->insert('seasons_to_suppliers',[
                    'supplier_id'=>$to_id,
                    'season_id'=>$v['season_id'],
                    'parent_id'=>$v['parent_id'],
                    'type_id'=>$v['type_id'],
                    'priority'=>$v['priority'],
                     
                    
                ])->execute();
            }
        }
        
        Yii::$app->db->createCommand()->delete('seasons_to_private_suppliers',['supplier_id'=>$to_id])->execute();
        if(!empty($data['season_private'])){
            foreach ($data['season_private'] as $v){
                Yii::$app->db->createCommand()->insert('seasons_to_private_suppliers',[
                    'supplier_id'=>$to_id,
                    'season_category_id'=>$v['season_category_id'],
                    'object_id'=>$v['object_id'],
                    'group_id'=>$v['group_id'],
                     
                    
                ])->execute();
            }
        }
        
        
        
        // Package
        Yii::$app->db->createCommand()->delete('package_to_supplier',['supplier_id'=>$to_id])->execute();
        if(!empty($data['package'])){
            foreach ($data['package'] as $v){
                Yii::$app->db->createCommand()->insert('package_to_supplier',[
                    'supplier_id'=>$to_id,
                    'package_id'=>$v['id'],
 
                ])->execute();
            }
        }
        
        // NQT
        Yii::$app->db->createCommand()->delete('nationality_groups_to_supplier',['supplier_id'=>$to_id])->execute();
        if(!empty($data['nationality_group'])){
            foreach ($data['nationality_group'] as $v){
                Yii::$app->db->createCommand()->insert('nationality_groups_to_supplier',[
                    'supplier_id'=>$to_id,
                    'group_id'=>$v['id'],
                    
                ])->execute();
            }
        }
        
        // Fit Git
        $group = [];
        if(!empty($data['guest_group'])){
            foreach ($data['guest_group'] as $v){
                $g = (new \yii\db\Query())->from('rooms_groups')->where(['pmin'=>$v['pmin'],'pmax'=>$v['pmax'],'parent_id'=>$to_id])->one();
                if(!empty($g)){
                    $group[$v['id']] = $g['id'];
                }else{
                    $group[$v['id']] = Yii::$app->zii->insert('rooms_groups',[
                        'title'=>$v['title'],
                        'note'=>$v['note'],
                        'pmin'=>$v['pmin'],
                        'pmax'=>$v['pmax'],
                        'parent_id'=>$to_id,
                        'sid'=>__SID__
                    ]);
                }
            }
        }
        Yii::$app->db->createCommand()->delete('rooms_groups',['and',
            ['parent_id'=>$to_id,'sid'=>__SID__],
            ['not in', 'id', !empty($group) ? array_values($group) : []]
        ])->execute();
        
        // Quotation
        $quot = [];
        if(!empty($data['quotation'])){
            foreach ($data['quotation'] as $v){
                $g = (new \yii\db\Query())->from('supplier_quotations')->where(['from_date'=>$v['from_date'],'to_date'=>$v['to_date'],'supplier_id'=>$to_id])->one();
                if(!empty($g)){
                    $quot[$v['id']] = $g['id'];
                }else{
                    $quot[$v['id']] = Yii::$app->zii->insert('supplier_quotations',[
                        'title'=>$v['title'],
                        'from_date'=>$v['from_date'],
                        'to_date'=>$v['to_date'],
                        'currency'=>$v['currency'],
                        'supplier_id'=>$to_id,
                        'sid'=>__SID__
                    ]);
                    
                    Yii::$app->db->createCommand()->insert('supplier_quotations_to_supplier', [
                        'quotation_id'=>$quot[$v['id']],
                        'supplier_id'=>$to_id,
                    ])->execute();
                }
            }
        }
        
        Yii::$app->db->createCommand()->delete('supplier_quotations',['and',
            ['supplier_id'=>$to_id,'sid'=>__SID__],
            ['not in', 'id', !empty($quot) ? array_values($quot) : []]
        ])->execute();
        
        //
        switch ($item['type_id']){
            case TYPE_ID_HOTEL: case TYPE_ID_SHIP_HOTEL:
                // Phòng
                Yii::$app->db->createCommand()->delete('rooms_to_hotel',['parent_id'=>$to_id])->execute();
                if(!empty($data['rooms'])){
                    foreach ($data['rooms'] as $room){
                        Yii::$app->db->createCommand()->insert('rooms_to_hotel',[
                            'parent_id'=>$to_id,
                            'room_id'=>$room['id'],
                            'quantity'=>$room['quantity'],
                            'is_default'=>$room['is_default'],
                            'has_extra_bed'=>$room['has_extra_bed']
                        ])->execute();
                    }
                }
                
                // Price
                Yii::$app->db->createCommand()->delete('rooms_to_prices',['supplier_id'=>$to_id])->execute();
                if(!empty($data['price_list'])){
                    foreach ($data['price_list'] as $v){
                        $v['supplier_id'] = $to_id;
                        $v['group_id'] = $group[$v['group_id']];
                        $v['quotation_id'] = $quot[$v['quotation_id']];
                        
                        Yii::$app->db->createCommand()->insert('rooms_to_prices',$v)->execute();
                    }
                }
                
                break;
            case TYPE_ID_REST:
                // Phòng
                
               // $existed_menu = \app\modules\admin\models\Menus::getMenus(['supplier_id'=>$to_id]);
               
                $menus = [];
                
                if(!empty($data['menus'])){
                    foreach ($data['menus'] as $km => $menu){
                        $g = (new \yii\db\Query())
                        ->select(['a.*'])
                        ->from(['a'=>'menus'])
                        ->innerJoin(['b'=>'menus_to_suppliers'],'a.id=b.menu_id') 
                        ->where(['b.supplier_id'=>$to_id,'a.title'=>$menu['title']])->one();
                        
                        if(!empty($g)){
                            $menus[$menu['id']] = $g['id'];
                        }else{
                            $menus[$menu['id']] = Yii::$app->zii->insert('menus',[
                                'sid'=>__SID__,
                                'title'=>$menu['title'],
                                'type_id'=>$menu['type_id'],
                                'supplier_id'=>$to_id,                                                     
                            ]);
                            
                            Yii::$app->db->createCommand()->insert('menus_to_suppliers', ['menu_id'=>$menus[$menu['id']], 'supplier_id'=>$to_id])->execute();
                        }
                        
                        $foods = Yii::$app->db->createCommand("SELECT * FROM `foods_to_menus` where menu_id=".$menu['id'])->queryAll();
                        
                        Yii::$app->db->createCommand()->delete('foods_to_menus',['menu_id'=>$menus[$menu['id']]])->execute();
                        if(!empty($foods)){
                            foreach ($foods as $food){
                                Yii::$app->db->createCommand()->insert('foods_to_menus',[
                                    'food_id'=>$food['food_id'],
                                    'menu_id'=>$menus[$menu['id']],
                                    'position'=>$food['position'],
                                     
                                ])->execute();
                            }
                        }
                        
                        
                        
                        
                    }
                }
                Yii::$app->db->createCommand()->delete('menus_to_suppliers',['and',
                    ['supplier_id'=>$to_id],
                    ['not in', 'menu_id', !empty($menus) ? array_values($menus) : []]
                ])->execute();

                
                
                // Price
                Yii::$app->db->createCommand()->delete('menus_to_prices',['supplier_id'=>$to_id])->execute();
                if(!empty($data['price_list'])){
                    foreach ($data['price_list'] as $v){
                        $v['supplier_id'] = $to_id;
                        $v['group_id'] = $group[$v['group_id']];
                        $v['quotation_id'] = $quot[$v['quotation_id']];
                        $v['item_id'] = $menus[$v['item_id']];
                        Yii::$app->db->createCommand()->insert('menus_to_prices',$v)->execute();
                    }
                }
                
                
                break;
                
            case TYPE_ID_VEHICLE:
                $listCar = Yii::$app->db->createCommand("SELECT * FROM `vehicles_to_cars` where parent_id=$from_id")->queryAll();
                Yii::$app->db->createCommand()->delete('vehicles_to_cars',['parent_id'=>$to_id])->execute();
                if(!empty($listCar)){
                    foreach ($listCar as $v){
                        $v['parent_id'] = $to_id;
                         
                        Yii::$app->db->createCommand()->insert('vehicles_to_cars',$v)->execute();
                    }
                }
                
                // Chặng
                $table = 'distances_to_suppliers';
                $listCar = Yii::$app->db->createCommand("SELECT * FROM `$table` where supplier_id=$from_id")->queryAll();
                Yii::$app->db->createCommand()->delete($table,['supplier_id'=>$to_id])->execute();
                if(!empty($listCar)){
                    foreach ($listCar as $v){
                        $v['supplier_id'] = $to_id;
                         
                        Yii::$app->db->createCommand()->insert($table,$v)->execute();
                    }
                }
                
                // Price
                $table = 'distances_to_prices';
                $listCar = Yii::$app->db->createCommand("SELECT * FROM `$table` where supplier_id=$from_id")->queryAll();
                Yii::$app->db->createCommand()->delete($table,['supplier_id'=>$to_id])->execute();
                if(!empty($listCar)){
                    foreach ($listCar as $v){
                        $v['supplier_id'] = $to_id;
                        $v['group_id'] = $group[$v['group_id']];
                        $v['quotation_id'] = $quot[$v['quotation_id']];
                         
                        Yii::$app->db->createCommand()->insert($table,$v)->execute();
                    }
                }
                
                $table = 'vehicles_to_prices';
                $listCar = Yii::$app->db->createCommand("SELECT * FROM `$table` where supplier_id=$from_id")->queryAll();
                Yii::$app->db->createCommand()->delete($table,['supplier_id'=>$to_id])->execute();
                if(!empty($listCar)){
                    foreach ($listCar as $v){
                        $v['supplier_id'] = $to_id;
                        $v['group_id'] = $group[$v['group_id']];
                        $v['quotation_id'] = $quot[$v['quotation_id']];
                         
                        Yii::$app->db->createCommand()->insert($table,$v)->execute();
                    }
                }
                
                
                break;
        }
         
        return $to_id;
    }
    
    
    
    
    public function getItemData($item_id){
        $data = [];
        $data['data'] = Yii::$app->db->createCommand("select * from {$this->tableName()} where id=$item_id")->queryOne(null,false);
        $data['place'] = \app\modules\admin\models\Customers::getCustomerPlaces($item_id);
        switch ($data['data']['type_id']){
            case TYPE_ID_HOTEL: case TYPE_ID_SHIP_HOTEL:
                $data['rooms'] = Yii::$app->tour->hotel->getRooms($item_id);
                $data['price_list'] = Yii::$app->db->createCommand("SELECT * FROM `rooms_to_prices` where supplier_id=$item_id")->queryAll();
                break;
                
            case TYPE_ID_REST:
                $data['menus'] = \app\modules\admin\models\Menus::getMenus(['supplier_id'=>$item_id]);
                $data['price_list'] = Yii::$app->db->createCommand("SELECT * FROM `menus_to_prices` where supplier_id=$item_id")->queryAll();
                break;
        }
        $data['season'] =  Yii::$app->customer->season->getListBySupplier($item_id);
        $data['season_child'] =  (new \yii\db\Query())->from('seasons_to_suppliers')->where(['supplier_id'=>$item_id])->all();
        $data['season_private'] =  (new \yii\db\Query())->from('seasons_to_private_suppliers')->where(['supplier_id'=>$item_id])->all();
        
        if(!empty($data['season'])){
            foreach ($data['season'] as $k=> $season){
                if(in_array($season['type_id'], [3,4,5])){
                    $data['season'][$k]['listChilds'] = \app\modules\admin\models\Seasons::get_list_weekend_by_parent($item_id, $season['id']);
                }else{
                    $data['season'][$k]['listChilds'] = \app\modules\admin\models\Seasons::get_list_seasons_by_parent($item_id, $season['id']);
                }
            }
        }
        
        // 
        $data['quotation']  =   \app\modules\admin\models\Customers::getSupplierQuotations($item_id);
        $data['package']    =   \app\modules\admin\models\PackagePrices::getPackages($item_id,false);
        $data['nationality_group']   =   \app\modules\admin\models\NationalityGroups::get_supplier_group($item_id);
        $data['guest_group']    =    \app\modules\admin\models\Seasons::get_rooms_groups($item_id,false);
        
        
        
        return $data;
    }
    
    
    public function createGroup($data)
    {
        $g = new CustomerGroups();
        
        $data['sid'] = __SID__;
        
        if(!isset($data['name']) && isset($data['title'])){
            $data['name'] = $data['title'];
        }
        
        foreach ($data as $k=>$v){
            $g->$k = $v;
        }
        
        if(!$g->save()){
            $g->save(false);
        }
        
        return Yii::$app->db->getLastInsertID();
    }
    
    public function getGroups($type_id)
    {
        return CustomerGroups::find()->where(['type_id' => $type_id, 'sid' => __SID__])->orderBy(['plevel' => SORT_DESC])->asArray()->all();
    }
    
    public function getGroupsByCustomer($customer_id, $type_id = -1)
    {
        $key1 = md5(__METHOD__);
        $key2 = md5(json_encode([$customer_id, $type_id]));
        
        if(isset($this->_cache[$key1][$key2])){
            return $this->_cache[$key1][$key2];
        }
        
        $query = static::find()
        ->select(['a.*'])
        ->from(['a' => CustomerGroups::tableName()])
        ->innerJoin(['b' => CustomersToGroups::tableName()], 'a.id=b.group_id')
        ->where(['b.customer_id' => $customer_id]);
        
        if($type_id > -1){
            $query->andWhere(['a.type_id' => $type_id]);
        }
        
//         view($query->createCommand()->getRawSql());
        
        return ($this->_cache[$key1][$key2] = $query->orderBy(['a.plevel' => SORT_DESC])->asArray()->all());
    }
    
    
    public function getGroupNameByCustomer($customer_id, $type_id = -1, $field = 'title')
    {
        $items = $this->getGroupsByCustomer($customer_id, $type_id);
        $rs = [];
         
        
        if(!empty($items)){
            foreach($items as $item){
                $rs[] = $item[$field];
            }
        }
        return $rs;
    }
    
    
    private $_cache;
    public function getGroupIdByCustomer($customer_id, $type_id = -1, $field = 'id')
    {
        $key1 = md5(__METHOD__);
        $key2 = md5(json_encode([$customer_id, $type_id, $field]));
        
        if(isset($this->_cache[$key1][$key2])){
            return $this->_cache[$key1][$key2];
        }
        
        $items = $this->getGroupsByCustomer($customer_id, $type_id);
        $rs = [];
        
        
        if(!empty($items)){
            foreach($items as $item){
                $rs[] = $item[$field];
            }
        }
        
        $this->_cache[$key1][$key2] = $rs;
        
        return $rs;
    }
    
    
    
    public function getSingleGroupIdByCustomer($customer_id, $type_id = -1, $field = 'id')
    {
        $key1 = md5(__METHOD__);
        $key2 = md5(json_encode([$customer_id, $type_id, $field]));
        
//         if(isset($this->_cache[$key1][$key2])){
//             return $this->_cache[$key1][$key2];
//         }
        
        $items = $this->getGroupsByCustomer($customer_id, $type_id);
//         $rs = [];

//         view($items);
        
        
        if(!empty($items)){
            foreach($items as $item){
                return $item[$field];
            }
        }
        
        //$this->_cache[$key1][$key2] = $rs;
        
        //return $rs;
    }
    
    
    
    public function countCustomerByGroup($group_id)
    {
        return CustomersToGroups::find()->where(['group_id' => $group_id])->count(1);         
    }
    
    
    public function addCustomerToGroup($customer_id, $group_id)
    {
        if(!is_array($group_id)){
            $groups = [$group_id];
        }else{
            $groups = $group_id;
        }
        
        if(!empty($groups)){
            foreach ($groups as $group_id){
                
                Yii::$app->db->createCommand()->insert(CustomersToGroups::tableName(), ['customer_id' => $customer_id, 'group_id' => $group_id])->execute();
            }
        }
    }
    
    public function removeCustomerGroups($customer_id, $type_id = -1)
    {
        $con = ['and', ['customer_id' => $customer_id]];
        
        if($type_id > -1){
            $con[] = [
                'group_id' => (new \yii\db\Query())->from(CustomerGroups::tableName())->where(['type_id' => $type_id, 'sid' => __SID__])->select('id'),
            ];
        }
        
        Yii::$app->db->createCommand()->delete(CustomersToGroups::tableName(), $con)->execute();
    }
    
    
    public function resetCustomerPassword($customer_id)
    {
        $data = [];
        
        $cus = Customer::findOne(['id' => $customer_id]);
        
        $password = randString(6);
        $data['password_hash'] = Yii::$app->security->generatePasswordHash($password);
        $data['updated_at'] = time();
        $data['auth_key'] = Yii::$app->security->generateRandomString();
        
        Yii::$app->db->createCommand()->update(Customer::tableName(), $data, ['id'=>$customer_id,'sid'=>__SID__])->execute();
        
        // Gửi email
        if(!empty($cus) && validateEmail($cus->email)){
            
            $fx = Yii::$app->getConfigs('CONTACTS');
            
            
            $search = array(
                '{LOGO}',
                '{DOMAIN}',
                '{USER}',
                '{USER_NAME}',
                '{USER_PASSWORD}',
                '{ADMIN_LINK}',
                '{LINK}',
                
            );
            $replace = array(
                isset(Yii::$app->config['logo']['logo']['image']) ? '<img src="' . getAbsoluteUrl(Yii::$app->config['logo']['logo']['image']) .'" style="max-height:90px"/>' : '',
                $fx['name'],
                isset($cus['name']) && $cus['name'] != "" ? $cus['name'] : $cus['email'],
                $cus['username'] != "" ? $cus['username'] : $cus['email'],
                $password,
                '[đã gửi]',
                '',
                
            );
            //     		$text = Yii::$app->izi->getTextRespon(array('code'=>'RP_SENDPASS', 'show'=>false));
            $text = Yii::$app->izi->getTextRespon(array('code'=>'RP_SENDPASS', 'default' => true,'lang'=>false, 'show'=>false));
            
            
            $form = str_replace($search, $replace, uh($text['value'],2));
            
            Yii::$app->mailer->sendEmail([
                'subject'=>str_replace($search, $replace, $text['title'])  ,
                'body'=>$form,
                'from'=>$fx['email'],
                'fromName'=>$fx['short_name'],
                'replyTo'=>$fx['email'],
                'replyToName'=>$fx['short_name'],
                'to'=>$cus['email'],'toName'=>$cus['lname'] . ' ' . $cus['fname']
            ]);
        }
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
}
