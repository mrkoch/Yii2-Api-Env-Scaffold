<?php
namespace  common\traits;

use Yii;
use common\models\utility\UtlNote;

trait Annotable{
	
	public function addNote( $note ) {
		
		$nota = new UtlNote;
		$nota->note = $note;
		$nota->model_name = static::className();
		$nota->id_model = $this->id;
		$nota->save();

	}

	/**
     * Sostituzione file
     * @return [type] [description]
     */
    public function getNote() {
        return $this->hasMany( UtlNote::className(), ['id_model'=>'id'])->where(['model_name'=>static::className()]);
    }
}