<?php

namespace izi\template\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use izi\template\models\Templates;

/**
 * TemplatesSearch represents the model behind the search form of `izi\template\models\Templates`.
 */
class TemplatesSearch extends Templates
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'parent_id', 'state', 'is_active', 'is_mobile', 'is_extension', 'is_invisible'], 'integer'],
            [['name', 'ref_code', 'title', 'layout', 'bizrule'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Templates::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'state' => $this->state,
            'is_active' => $this->is_active,
            'is_mobile' => $this->is_mobile,
            'is_extension' => $this->is_extension,
            'is_invisible' => $this->is_invisible,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'ref_code', $this->ref_code])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'layout', $this->layout])
            ->andFilterWhere(['like', 'bizrule', $this->bizrule]);

        return $dataProvider;
    }
}
