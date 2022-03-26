<?php 
namespace api\utils;

use api\utils\ResponseError;
use common\models\blocked\BlkIp;
/**
 * Se indirizzo ip bloccato impedisce ogni chiamata, componente inizializzato al bootstrap
 */
class FilterIps extends \yii\base\Component{
    public function init() {

    	$r = new \yii\web\Request();
        if (BlkIp::find()->where(['ip'=>$r->getUserIP()])->count() > 0) ResponseError::returnSingleError( 403, "Indirizzo ip bloccato");
        
        parent::init();
    }
}