<?php

namespace common\models\locations;
use common\models\persons\Persons;
use common\models\persons\PersonsInfo;
use common\traits\Searchable;

use Yii;

/**
 * This is the model class for table "loc_nation".
 *
 * @property string $id Corrisponde all'identificativo internazionale ISO 3166-1 alpha-2
 * @property int $idcontinent
 * @property string $name
 * @property string $name_en
 * @property string $continent
 * @property string $ts
 */
class LocNation extends \yii\db\ActiveRecord
{

    use Searchable;

    public $query_attrs = [
        'id' => ['php_func', 'strtoupper', '=','loc_nation.id'],
        'idcontinent' => ['=', 'loc_nation.idcontinent'],
        'search' => ['ilike', 'loc_nation.name'],
    ];

    /**
     * Group by di default per Trait Searchable
     * @var array
     */
    public $groupBy = ['loc_nation.id'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'loc_nation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
          [['id'], 'required'],
          [['ts'], 'safe'],
          [['id'], 'integer'],
          [['name', 'name_en'], 'string', 'max' => 50],
          [['continent'], 'string', 'max' => 10],
          [['id'], 'unique'],
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
          'continent' => Yii::t('app', 'Continent'),
          'ts' => Yii::t('app', 'Ts'),
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
