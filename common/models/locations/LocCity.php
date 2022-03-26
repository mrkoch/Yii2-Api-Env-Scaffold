<?php

namespace common\models\locations;

use common\models\persons\Persons;
use Yii;
use common\traits\Searchable;

/**
 * This is the model class for table "loc_city".
 *
 * @property string $id Corrisponde al codice catastale
 * @property string $codistat
 * @property string $name
 * @property string $zip
 * @property string $phone_prefix
 * @property string $id_province
 * @property string $id_region
 * @property string $codnuts3
 * @property bool $isprovince flag che indica se è capoluogo di provincia
 * @property string $ts
 * @property integer $code_asl_district
 *
 * @property LocProvince $province
 * @property LocRegion $region
 *
 */
class LocCity extends \yii\db\ActiveRecord
{

    use Searchable;

    public $query_attrs = [
        'id' => ['=','loc_city.id'],
        'id_region' => ['=', 'loc_city.id_region'],
        'id_province' => ['=', 'loc_city.id_province'],
        'search' => ['likestart', 'loc_city.location'],
        'location' => ['=', 'loc_city.location'],
    ];

    /**
     * With di default per Trait Searchable
     * @var array
     */
    //public $defaultWiths = ['province'];

    /**
     * Group by di default per Trait Searchable
     * @var array
     */
    public $groupBy = ['loc_city.id'];



    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'loc_city';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
          [['id', 'location', 'id_province', 'id_region', 'codnuts3'], 'required'],
          [['isprovince'], 'boolean'],
          [['ts'], 'safe'],
          [['id', 'code_asl_district'], 'integer'],
          [['codistat'], 'string', 'max' => 10],
          [['location'], 'string', 'max' => 50],
          [['zip', 'phone_prefix', 'codnuts3'], 'string', 'max' => 5],
          [['id_province'], 'string', 'max' => 2],
          [['id_region'], 'string', 'max' => 3],
          [['id'], 'unique'],
          [['id_province'], 'exist', 'skipOnError' => true, 'targetClass' => LocProvince::className(), 'targetAttribute' => ['id_province' => 'id']],
          [['id_region'], 'exist', 'skipOnError' => true, 'targetClass' => LocRegion::className(), 'targetAttribute' => ['id_region' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
          'id' => Yii::t('app', 'ID'),
          'codistat' => Yii::t('app', 'Codistat'),
          'location' => Yii::t('app', 'Location'),
          'zip' => Yii::t('app', 'Zip'),
          'phone_prefix' => Yii::t('app', 'Phone Prefix'),
          'id_province' => Yii::t('app', 'Idprovince'),
          'id_region' => Yii::t('app', 'Idregion'),
          'codnuts3' => Yii::t('app', 'Codnuts3'),
          'isprovince' => Yii::t('app', 'Isprovince'),
          'ts' => Yii::t('app', 'Ts'),
          ];
    }

    public function fields(){
        return array_merge(parent::fields(),[
            'label' => function($model) {
                return $model->location;
            }
        ]);
    }

   /**
    * @return \yii\db\ActiveQuery
    */
   public function getProvince()
   {
      return $this->hasOne(LocProvince::className(), ['id' => 'id_province']);
   }

   /**
    * @return \yii\db\ActiveQuery
    */
   public function getRegion()
   {
      return $this->hasOne(LocRegion::className(), ['id' => 'id_region']);
   }
}
