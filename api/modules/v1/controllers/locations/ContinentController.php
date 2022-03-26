<?php

namespace api\modules\v1\controllers\locations;

use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use sizeg\jwt\JwtHttpBearerAuth;
use common\models\locations\LocContinent;


class ContinentController extends ActiveController
{
    public $modelClass = 'common\models\locations\LocContinent';

    public function actions()
    {
        $actions = parent::actions();
//        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
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
     * Index region
     * @param   string|null $search
     * @return  ActiveDataProvider
     */
    public function actionIndex() {
        $model = new LocContinent();
        return new ActiveDataProvider([
            'query' => $model->search(),
            'pagination' => Yii::$app->request->get('pagination') ? ['pagesize' => 20] : false
        ]);
    }    

}