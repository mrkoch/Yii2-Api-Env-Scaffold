<?php

namespace api\modules\v1\controllers\locations;

use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use sizeg\jwt\JwtHttpBearerAuth;
use common\models\locations\LocCity;

class CityController extends ActiveController
{
    public $modelClass = 'common\models\locations\LocCity';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
            'except' => ['options']
        ];

        return $behaviors;
    }

    /**
     * Di default per il metodo options torniamo ok in modo da non avere errori not found dalle chiamate automatiche del browser
     * @return [type] [description]
     */
    public function actionOptions() {
        return ['message'=>'ok'];
    }

    /**
     * Index city
     * @param   string|null $search
     * @return  ActiveDataProvider
     */
    public function actionIndex() {

        $limit = Yii::$app->request->get('limit') ? Yii::$app->request->get('limit') : 20;

        $model = new LocCity();
        return new ActiveDataProvider([
            'query' => $model->search()->orderBy(['location'=>SORT_ASC]),
            'pagination' => Yii::$app->request->get('pagination') ? ['pagesize' => $limit] : false
        ]);
    }    

}