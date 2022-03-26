<?php

namespace common\models\locations;

use Yii;
use common\traits\Searchable;

/**
 * This is the model class for table "loc_continent".
 *
 * @property int $id
 * @property string $name
 * @property string $name_en
 */
class LocContinent extends \yii\db\ActiveRecord
{


    use Searchable;

    public $query_attrs = [
        'search' => ['ilike','loc_continent.name'],
    ];

    /**
     * Group by di default per Trait Searchable
     * @var array
     */
    public $groupBy = ['loc_continent.id'];



    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {

        return 'loc_continent';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 7],
            [['name_en'], 'string', 'max' => 9],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
          'id' => Yii::t('app', 'ID'),
          'name' => Yii::t('app', 'Name'),
          'name_en' => Yii::t('app', 'Name En'),
        ];
    }

    public function fields(){
        return array_merge(parent::fields(),[
            'label' => function($model) {
                return $model->name;
            }

        ]);
    }
}
