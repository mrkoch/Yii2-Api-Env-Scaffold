<?php

namespace common\models\locations;

use Yii;

/**
 * This is the model class for table "loc_district".
 *
 * @property int $id
 * @property string $idcity
 * @property string $codistat
 * @property string $city
 * @property int $code Codice della circoscrizione
 * @property string $name Nome della circoscrizione
 *
 * @property LocAsl[] $locAsls
 * @property LocCity $city0
 */
class LocDistrict extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'loc_district';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codistat', 'code', 'name'], 'required'],
            [['code'], 'default', 'value' => null],
            [['code'], 'integer'],
            [['idcity'], 'string', 'max' => 4],
            [['codistat', 'city'], 'string', 'max' => 10],
            [['name'], 'string', 'max' => 15],
            [['idcity'], 'exist', 'skipOnError' => true, 'targetClass' => LocCity::className(), 'targetAttribute' => ['idcity' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'idcity' => Yii::t('app', 'Idcity'),
            'codistat' => Yii::t('app', 'Codistat'),
            'city' => Yii::t('app', 'City'),
            'code' => Yii::t('app', 'Code'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocAsls()
    {
        return $this->hasMany(LocAsl::className(), ['iddistrict' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity0()
    {
        return $this->hasOne(LocCity::className(), ['id' => 'idcity']);
    }
}
