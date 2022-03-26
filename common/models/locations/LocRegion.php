<?php

namespace common\models\locations;

use Yii;
use common\traits\Searchable;


/**
 * This is the model class for table "loc_region".
 *
 * @property int $id
 * @property string $region
 */
class LocRegion extends \yii\db\ActiveRecord
{

    use Searchable;

    public $query_attrs = [
        'id' => ['=','loc_region.id'],
        'search' => ['ilike','loc_region.region'],
        'region' => ['=','loc_region.region'],

    ];

    /**
     * Group by di default per Trait Searchable
     * @var array
     */
    public $groupBy = ['loc_region.id'];



    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'loc_region';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'string', 'max' => 3],
            [['region'], 'string', 'max' => 30],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'region' => 'Nome',
        ];
    }

    public function fields(){
        return array_merge(parent::fields(),[
            'label' => function($model) {
                return $model->region;
            }

        ]);
    }
}
