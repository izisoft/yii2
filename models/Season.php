<?php
namespace izi\models;

use Yii;
use yii\db\ActiveRecord;

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
class Season extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%seasons}}';
    }
    
    public static function tableWeekend(){
        return '{{%weekend}}';
    }
    public static function tableCategory(){
        return '{{%seasons_categorys}}';
    }
    public static function tableToSuppliers(){
        return '{{%seasons_to_suppliers}}';
    }
    public static function tableCategoryToSupplier(){
        return '{{%seasons_categorys_to_suppliers}}';
    }
    public static function tableCategoryToServices(){
        return '{{%seasons_categorys_to_services}}';
    }
    
    
    public function getListBySupplier($supplier_id, $o=[]){
        $price_type = isset($o['price_type']) ? $o['price_type'] : [];
        $default = isset($o['default']) && $o['default'] == true ? true : false;
        $type_id = isset($o['type_id']) ? $o['type_id'] : [];
        $query = static::find()
        ->from(['a'=>Season::tableCategory()])
        ->innerJoin(['b'=>Season::tableCategoryToSupplier()],'b.season_id=a.id');
        $query
        ->select(['a.*','b.*'])       
        ->where([
            'a.sid'=>__SID__,'b.supplier_id'=>$supplier_id
        ])->andWhere(['>','a.state',-2])
        ;
        if(is_numeric($price_type) || !empty($price_type)){
            
            $query->andWhere(['b.price_type'=>$price_type]);
        }
        if(is_numeric($type_id) || !empty($type_id)){
            $query->andWhere(['a.type_id'=>$type_id]);
        }
        $r = $query->orderBy(['b.priority'=>SORT_ASC,'a.type_id'=>SORT_ASC,'a.title'=>SORT_ASC])->asArray()->all();
        if($default && empty($r)){
            $r = [
                ['id'=>0,'title'=>'Mặc định']
            ];
        }
        return $r;
    }
    
    public function setPrioritySupplier($supplier_id, $season_id, $priority){
        Yii::$app->db->createCommand()->update(Season::tableCategoryToSupplier(), ['priority'=>$priority],[
            'supplier_id'=>$supplier_id,
            'season_id'=>$season_id
        ])->execute();
    }
    
    /**
     * Get season from date
     */
    
    public function getSeasonFromDate($o){
        $date = isset($o['date']) ? $o['date'] : date('Y-m-d');
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $time_id = isset($o['time_id']) ? $o['time_id'] : -1;
        if(!check_date_string($date)){
            $date = date('Y-m-d');
        }else{
            $date = ctime(['string'=>$date,'format'=>'Y-m-d']);
        }
        
        // Danh sách mùa lấy theo ngày $date
        $query = static::find()->from(['a'=>Season::tableCategory()])
        ->innerJoin(['b'=>Season::table_category_to_supplier()],'a.id=b.season_id')
        ->where(['and',
            ['a.sid'=>__SID__,
                'b.supplier_id'=>$supplier_id,
                'a.type_id'=>[SEASON_TYPE_SERVICE],
                //'b.price_type'=>[0]
            ],
            ['>','a.state',-2]
            
        ])->andWhere([
            'a.id'=>(new \yii\db\Query())->from(['c'=>Season::tableName()])
            ->innerJoin(['d'=>Season::tableToSuppliers()],'c.id=d.season_id')
            ->where([
                'd.supplier_id'=>$supplier_id,
                'd.type_id'=>[SEASON_TYPE_SERVICE]
            ])
            ->andWhere("'$date' between c.from_date and c.to_date")
            ->select('d.parent_id')
        ]);
        
        $r['seasons'] = $query->orderBy(['b.price_type'=>SORT_ASC])->asArray()->all();
        $r['direct_seasons_prices'] = $r['incurred_seasons_prices'] = [];
        //
        
        // Danh sách cuối tuần, ngày thường
        
        $thu_trong_tuan = date('w',strtotime($date));
        $ctt = $thu_trong_tuan == 0 ? 7 : $thu_trong_tuan;
        //
        $sub_query = (new \yii\db\Query())->from(['c'=>Season::tableWeekend()])
        ->innerJoin(['d'=>Season::tableToSuppliers()],'c.id=d.season_id')
        ->where([
            'd.supplier_id'=>$supplier_id,
            'd.type_id'=>[SEASON_TYPE_WEEKEND,SEASON_TYPE_WEEKDAY],
        ]);
        if($thu_trong_tuan == 0){
            $sub_query->andWhere("
($thu_trong_tuan between c.from_date and c.to_date)
                
                
or ($ctt between c.from_date and c.to_date)");
        }else{
            $sub_query->andWhere("($thu_trong_tuan between c.from_date and c.to_date)
                
");
        }
        
        
        $sub_query->select('d.parent_id');
        if($time_id > -1){
            $t = configPartTime()[$time_id];
            
            $sub_query
            
            ->andWhere("
					1=(case when c.to_date = $ctt then
					(case when
					c.to_time >= '".$t['to_time']."'
					then 1 else 0 end
					)
					 when c.from_date = $ctt then
(case when
					c.from_time <= '".$t['from_time']."'
					then 1 else 0 end
					)
                
					else 1
					end)
			")
			
			//->andWhere(['<=','c.to_time',$t['to_time']])
            //->andWhere(['>=','c.to_time',$t['to_time']])
            ;
            
            
        }
        //
        $query = (new \yii\db\Query())->from(['a'=>Season::tableCategory()])
        ->innerJoin(['b'=>Season::table_category_to_supplier()],'a.id=b.season_id')
        ->where(['and',
            ['a.sid'=>__SID__,
                'b.supplier_id'=>$supplier_id,
                'a.type_id'=>[SEASON_TYPE_WEEKEND,SEASON_TYPE_WEEKDAY],
                //'b.price_type'=>[0]
            ],
            ['>','a.state',-2]
            
        ])
        
        ->andWhere([
            'a.id'=>$sub_query
        ])
        
        ;
        
        //view($sub_query->createCommand()->getRawSql());
        
        $r['week_day'] = $query->orderBy(['b.price_type'=>SORT_ASC])->all();
        $r['week_day_prices'] = $query->andWhere(['b.price_type'=>[0]])->one();
        //view($query->createCommand()->getRawSql());
        // Danh sách buổi trong ngày
        
        $thu_trong_tuan = date('w',strtotime($date));
        //
        $sub_query = (new \yii\db\Query())->from(['c'=>Season::tableWeekend()])
        ->innerJoin(['d'=>Season::tableToSuppliers()],'c.id=d.season_id')
        ->where([
            'd.supplier_id'=>$supplier_id,
            'd.type_id'=>[SEASON_TYPE_TIME],
        ])
        ->andWhere("$thu_trong_tuan between c.from_date and c.to_date")
        
        ->select('d.parent_id');
        if($time_id > -1){
            $sub_query->andWhere(['c.part_time'=>$time_id])	;
        }
        //
        $query = (new \yii\db\Query())->from(['a'=>Season::tableCategory()])
        ->innerJoin(['b'=>Season::table_category_to_supplier()],'a.id=b.season_id')
        ->where(['and',
            ['a.sid'=>__SID__,
                'b.supplier_id'=>$supplier_id,
                'a.type_id'=>[SEASON_TYPE_TIME],
                //'b.price_type'=>[0]
            ],
            ['>','a.state',-2]
            
        ])
        
        ->andWhere([
            'a.id'=>$sub_query
        ])
        
        ;
        //view($query->createCommand()->getRawSql());
        
        $r['time_day'] = $query->orderBy(['b.price_type'=>SORT_ASC])->all();
        $r['time_day_prices'] = $query->andWhere(['b.price_type'=>[0]])->one();
        // Tổng hợp dữ liệu
        $rx = array_merge($r['seasons'],$r['week_day'],$r['time_day']);
        
        if(!empty($r['seasons'])){ $cPriceType0 = 0;
        foreach ($r['seasons'] as $k=>$v){
            
            if($v['price_type'] == 0){
                if($cPriceType0 == 0){
                    $r['seasons_prices'] = $v;++$cPriceType0;
                }
                $r['direct_seasons_prices'][] = $v;
            }else{
                $r['incurred_seasons_prices'][] = $v;
            }
            if(!isset($r['seasons_price_type_'.$v['price_type']])){
                $r['seasons_price_type_'.$v['price_type']][0] = $v;
            }else{
                $r['seasons_price_type_'.$v['price_type']][] = $v;
            }
            
        }
        }
        
        
        $directSeasons = isset($r['seasons_price_type_0']) ? $r['seasons_price_type_0'] : [];
        
        if(!empty($r['seasons_price_type_1'])){
            foreach ($r['seasons_price_type_1'] as $i){
                foreach (self::getDirectSeason([
                    'season_id'=>$i['id'],
                    'supplier_id'=>$supplier_id,
                    'default_direct'=>isset($r['seasons_price_type_0']) ? $r['seasons_price_type_0'] : [],
                ]) as $x){
                    $directSeasons[] = $x;
                }
            }
        }
        $r['season_direct_prices'] = $directSeasons;
        return $r;
    }
    
}