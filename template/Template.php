<?php 
namespace izi\template;

use Yii;
use izi\template\models\Templates;
use izi\template\models\TempToShop;
use izi\template\models\DomainPointer;

class Template extends \yii\base\Component
{
    
	private $_model;
    
    public function getModel(){
        if($this->_model === null){
            $this->_model = Yii::createObject('izi\template\models\Templates');
        }
        return $this->_model;
    }
	
	
	public function validate($params)
	{
		$id = isset($params['id']) ? $params['id'] : 0;
		$name = isset($params['name']) ? $params['name'] : '';
		if($name != ""){
			$item = Templates::find()->where([
			'and', ['name'=>$name],['not in', 'id', $id]
			])->one();			 
			if(!empty($item)) return false;
			return true;
		}
		return false;
	}
	/**
     * Creates a new Templates model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function create($params = null)
    {
        $model = new Templates();

		$data = !empty($params) ? $params : Yii::$app->request->post();
		
		if(!empty($params) ){
			$state = true;
			foreach($params as $k=>$v){
				$model->{$k} = $v;	
			}
		}else{
			$state = $model->load($data);
		}
		 
		
        if ($this->validate($data) && $state && $model->save()) {
            return true;
        }
		return false;
    }
	
	
	public function update($id, $params = null)
    {
        $model = Templates::findOne($id);


		$data = !empty($params) ? $params : Yii::$app->request->post();
		
		if(!empty($params) ){
			$state = true;
			foreach($params as $k=>$v){
				$model->{$k} = $v;	
			}
		}else{
			$state = $model->load($data);
		}
		 
		
        if ($this->validate([
		'id'=>$model->id,
		'name'=>$model->name,
		]) && $state && $model->save()) {
            return true;
        }
		return false;
    }
	
	public function remove($id)
    {
		if(Yii::$app->user->can([ROOT_USER, DEV_USER])){
			return $this->findModel($id)->delete();
		}
    }

    /**
     * Finds the Templates model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Templates the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Templates::findOne($id)) !== null) {
            return $model;
        }
 
    }

	public function assignment($temp_id, $sid = __SID__, $state = 1)
	{
		$model = TempToShop::find()->where(['sid'=>$sid, 'temp_id'=>$temp_id])->one();
		if(empty($model)){
			$model = new TempToShop();
		}
		
		$model->temp_id = $temp_id;
		$model->sid	= $sid;
		$model->state = $state;
		
		if ($model->save()) {
            return true;
        }
		return false;
	}
	
	public function assignmentDomain($temp_id, $domain)
	{
		$model = DomainPointer::find()->where(['sid'=>__SID__, 'domain'=>$domain])->one();
		if(empty($model)){
			return false;
		}
		
		$model->temp_id = $temp_id; 
		
		if ($model->save()) {
            return true;
        }
		return false;
	}
	
	public function validateDomain($domain)
	{
		$id = isset($params['id']) ? $params['id'] : 0;
		$name = isset($params['name']) ? $params['name'] : '';
		if($name != ""){
			$item = Templates::find()->where([
			'and', ['name'=>$name],['not in', 'id', $id]
			])->one();			 
			if(!empty($item)) return false;
			return true;
		}
		return false;
	}
	
	public function getVersions($id)
	{
		$model = $this->findModel($id);
		if(!empty($model) && !empty($data = json_decode($model->bizrule,1, JSON_UNESCAPED_UNICODE))){
			return isset($data['versions']) ? $data['versions'] : [];
		}
	}
	
	public function addVersion($id, $version)
	{
		$model = $this->findModel($id);
		if(!empty($model) ){
			$data = json_decode($model->bizrule,1, JSON_UNESCAPED_UNICODE);
			$versions = isset($data['versions']) ? $data['versions'] : [];
			
			
			if(!in_array($version, $versions)){
				$versions[] = $version;	
				
				$data['versions'] = $versions;
				
				$model->bizrule = json_encode($data, JSON_UNESCAPED_UNICODE);
				
				return $model->save();
			}
			
		}
		return false;
	}
	
	public function renameVersion($id, $old_version, $new_version)
	{
		$model = $this->findModel($id);
		if(!empty($model) ){
			$data = json_decode($model->bizrule,1, JSON_UNESCAPED_UNICODE);
			$versions = isset($data['versions']) ? $data['versions'] : [];
			
			
			if(!empty($versions) && in_array($old_version, $versions) && !in_array($new_version, $versions)){
				
				foreach($versions as $k=>$v){
					if($v == $old_version){
						$versions[$k] = $new_version;
						break;
					}
				}
				
				$data['versions'] = $versions;
				
				$model->bizrule = json_encode($data, JSON_UNESCAPED_UNICODE);
		 
				return $model->save();
			}
			
		}
		return false;
	}
	
	public function removeVersion($id, $version)
	{
		$model = $this->findModel($id);
		if(!empty($model) ){
			$data = json_decode($model->bizrule,1, JSON_UNESCAPED_UNICODE);
			$versions = isset($data['versions']) ? $data['versions'] : [];
			
			
			if(!empty($versions) && in_array($version, $versions)){
				
				foreach($versions as $k=>$v){
					if($v == $version){
						unset($versions[$k]);
						break;
					}
				}
				
				$data['versions'] = $versions;
				
				$model->bizrule = json_encode($data, JSON_UNESCAPED_UNICODE);
		 
				return $model->save();
			}
			
		}
		return false;
	}
	
	public function setVersion($id, $version)
	{
		$model = $this->findModel($id);
		if(!empty($model) ){
			$data = json_decode($model->bizrule,1, JSON_UNESCAPED_UNICODE);
			$versions = isset($data['versions']) ? $data['versions'] : [];
			
			
			if(!empty($versions) && in_array($version, $versions)){
 
				$data['versions'] = $versions;
				$data['version'] = $version;
				
				$model->bizrule = json_encode($data, JSON_UNESCAPED_UNICODE);
		 
				return $model->save();
			}
			
		}
		return false;
	}
	
	public function getVersion($id)
	{
		$model = $this->findModel($id);
		if(!empty($model) ){
			$data = json_decode($model->bizrule,1, JSON_UNESCAPED_UNICODE);
			return isset($data['version']) ? $data['version'] : null;
		}
 
	}
	
	
	public function getCtemplate($id){
		return $this->getModel()->getCtemplate($id);
	}
	
	public function getAllBlock(){
        return $this->getModel()->getAllBlock();
    }
	
	
	public function renderBlockTemplate($temp_id, $data = []){
        $fp = Yii::$app->viewPath . '/partials/page/' . $temp_id . '/index.php';

        if(file_exists($fp)){
            echo Yii::$app->renderPartial($fp);
        }
    }
    
    
    
}
