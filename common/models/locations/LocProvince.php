<?php

namespace common\models\locations;

use Yii;
use common\traits\Searchable;


/**
 * This is the model class for table "loc_province".
 *
 * @property string $id Corrisponde alla sigla univoca italiana
 * @property string $province
 * @property string $idregion
 * @property string $geographical_area
 * @property string $codnuts3
 * @property string $ts
 *
 * @property LocCity[] $locCities
 * @property LocRegion $region
 *
 */
class LocProvince extends \yii\db\ActiveRecord
{

    use Searchable;

    public $query_attrs = [
        'id' => ['=','loc_province.id'],
        'id_region' => ['=', 'loc_province.id_region'],
        'search' => ['ilike', 'loc_province.province'],
        'name' => ['=', 'loc_province.province'],

    ];

    /**
     * Group by di default per Trait Searchable
     * @var array
     */
    public $groupBy = ['loc_province.id'];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'loc_province';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
          [['id', 'province', 'idregion'], 'required'],
          [['ts'], 'safe'],
          [['id'], 'string', 'max' => 2],
          [['province'], 'string', 'max' => 50],
          [['idregion'], 'string', 'max' => 3],
          [['geographical_area'], 'string', 'max' => 20],
          [['codnuts3'], 'string', 'max' => 5],
          [['id'], 'unique'],
          [['idregion'], 'exist', 'skipOnError' => true, 'targetClass' => LocRegion::className(), 'targetAttribute' => ['idregion' => 'id']],
          ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
          'id' => Yii::t('app', 'ID'),
          'province' => Yii::t('app', 'Provincia'),
          'idregion' => Yii::t('app', 'Idregion'),
          'geographical_area' => Yii::t('app', 'Geographical Area'),
          'codnuts3' => Yii::t('app', 'Codnuts3'),
          'ts' => Yii::t('app', 'Ts'),
        ];
    }

    public function fields(){
        return array_merge(parent::fields(),[
            'label' => function($model) {
                return $model->province;
            }

        ]);
    }

   /**
    * @return \yii\db\ActiveQuery
    */
   public function getLocCities()
   {
      return $this->hasMany(LocCity::className(), ['idprovince' => 'id']);
   }

   /**
    * @return \yii\db\ActiveQuery
    */
   public function getRegion()
   {
      return $this->hasOne(LocRegion::className(), ['id' => 'idregion']);
   }
}
