<?php
namespace api\utils;

use Yii;

class ResponseError
{

	public static function returnSingleError($status, $string, $hideError = false)
    {
        Yii::$app->response->statusCode = $status;
        Yii::$app->response->data = ['name'=>'single_error', 'error'=>$string, 'hideError'=>$hideError];
        Yii::$app->response->send();
        die();
        //throw new \yii\web\HttpException( $status, json_encode( ['name'=>'single_error', 'message'=>$string] ) );    
    }

    public static function returnMultipleErrors($status, $array, $hideError = false)
    {
        Yii::$app->response->statusCode = $status;
        Yii::$app->response->data = ['name'=>'multiple_error', 'errors'=>$array, 'hideError'=>$hideError];
        Yii::$app->response->send();
        die();
        //throw new \yii\web\HttpException( $status, json_encode( ['name'=>'multiple_error', 'errors'=>$array] ) );    
    }

   /**
    * Restituisce multipli errori in un'unica stringa separati dal pipe
    * @param $errors
    * @return string
    */
   public static function multipleErrorsToString($errors)
   {
      return implode(" ", array_map(function ($a) {
         return implode(" | ", $a);
      }, $errors));
   }

}