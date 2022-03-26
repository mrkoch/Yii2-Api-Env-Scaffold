<?php
namespace api\modules\v1\controllers;

use api\utils\Functions;
use common\models\ConUserOrganization;
use common\models\hospitalization\Organizations;
use common\models\LoginForm;
use common\models\persons\Persons;
use common\models\rbac\AuthItem;
use Yii;

use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\data\ActiveDataFilter;

use yii\filters\AccessControl;
use sizeg\jwt\JwtHttpBearerAuth;
use api\utils\SendMail;

use common\models\User;
use common\models\user\UserChangePassword;
use common\models\user\UserLastPasswords;
use common\models\user\UserAllowedIps;

use api\utils\ResponseError;

use yii\db\Expression;


class UserController extends ActiveController
{
    public $modelClass = 'common\models\User';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors =  [
            'authenticator' => [
                'class' => JwtHttpBearerAuth::class,
                'except' => ['change-password','options','reset-password'],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => [
                    'update','allowIp','blockIp','removeBlockIp'
                ],
                'denyCallback' => function ($rule, $action) {
                    ResponseError::returnSingleError(401, "Non sei autorizzato per questa azione");
                },
                'rules' => [
//                    [
//                        'allow' => true,
//                        'actions' => ['create','changeUserPassword'],
//                        'permissions' => ['pUserCreate'],
//                        'verbs' => ['POST','OPTIONS'],
//                    ],
                    /*[
                        'allow' => true,
                        'actions' => ['update','allowIp','blockIp','removeBlockIp'],
                        'permissions' => ['pUserEdit'],
                        'verbs' => ['PUT','POST','OPTIONS'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'permissions' => ['pUserDelete'],
                        'verbs' => ['DELETE','OPTIONS'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index','view'],
                        'permissions' => ['pUserView'],
                        'verbs' => ['GET','OPTIONS'],
                    ]*/
                ],
            ]
        ];
        return $behaviors;

    }

    public function actions() {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        unset($actions['create']);
        unset($actions['delete']);
        return $actions;
    }

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'list',
    ];


    public function prepareDataProvider() {

    	$model = new User();
        $limit = Functions::getLimitPagination(Yii::$app->request->get());

        return new ActiveDataProvider([
            'query' => $model->search()->orderBy('username'),
            'pagination' => Yii::$app->request->get('pagination') ? ['pagesize' => $limit] : false
        ]);
    }

    public function actionDropDownItems() {

        $model = new User();
        $resultArray = $model->search()->select(['id','username'])->asArray()->all();
        
        $res=[];
        $res = array_map(function($item){
            return ['value'=>$item['id'], 'label'=>$item['username']];
        }, $resultArray);


        return $res;
    }

    /**
     * Di default per il metodo options torniamo ok in modo da non avere errori not found dalle chiamate automatiche del browser
     * @return [type] [description]
     */
    public function actionOptions() {
        return ['message'=>'ok'];
    }

    public function actionList(){
        return User::find()->orderBy('id')->all();
    }

    /**
     * @todo test
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function actionCreate( )
    {
        $conn = \Yii::$app->db;
        $dbTrans = $conn->beginTransaction();

        try {
//            $postData = Yii::$app->request->post();
//            $person = Persons::find()->where(['fiscal_code' => $postData['fiscal_code']])->one();
//            if(!$person){
//                $newPerson = new Persons;
//                $newPerson->load(['Persons' => $postData]);
//
//                if(!$newPerson->save()) ResponseError::returnMultipleErrors( 422, $newPerson->getErrors());
//            }

            $postData   = Yii::$app->request->post();

            $user = new User;

            $randomPassword= $user->setRandomPassword();
            $user->setPassword($randomPassword);
            $user->generateAuthKey();
            $user->expired_password=date("Y-m-d", strtotime("+1 month"));
//            $user->id_person = $person ? $person->id : $newPerson->id;
            $user->load(['User'=>$postData]);

            $user->is_temp_password = 1;

            if(!$user->save()) ResponseError::returnMultipleErrors( 422, $user->getErrors());

            // Set Roles
            if(!empty($postData['roles'])){
                foreach ($postData['roles'] as $role){
                    $auth = Yii::$app->authManager;
                    $role = $auth->getRole($role);
                    $auth->assign($role, $user->id);
                }
            }


            // Create Relation User-Organizations
//            foreach ($user->toArray([], ['organizations'])['organizations'] as $organization){
//                $user->unlinkAll('organization',$organization);
//            }

            // Set Organizations
            if(!empty($postData['organizations'])){
                foreach ($postData['organizations'] as $idOrganization){
                    $organization_object = Organizations::findOne($idOrganization);
                    if(!$organization_object) ResponseError::returnSingleError(404, "Struttura non trovato");
                    $user->link('organizations', $organization_object);
                }
            }


            $sent = SendMail::send(
                Yii::$app->params['adminEmail'],
                $user->email,
                'Nuovo Account',
                [],
                [
                    'use_layout'=>true,
                    'theme' => [
                        'html'=>'newUser-html',
                        'text'=>'newUser-text'
                    ],
                    'theme_vars' => [
                        'username' => $user->username,
                        'email'=>$user->email,
                        'password'=>$randomPassword,
                    ]
                ]
            );

            if(!$sent) ResponseError::returnSingleError( 500, "Errore invio mail");

            $dbTrans->commit();
            return $user;

        } catch (\Exception $e) {
            Yii::error($e);
            $dbTrans->rollBack();

            throw $e;
        }
    }

    public function actionUpdateUser($id){
        $conn = \Yii::$app->db;
        $dbTrans = $conn->beginTransaction();

        try {
            $postData   = Yii::$app->request->post();

            $id_user = Yii::$app->request->post('id_user');
            if($id_user){
                $user = user::findOne($id_user);
                $user->load(['User' => $postData]);
                $auth = Yii::$app->authManager;
                if(Yii::$app->request->post('roles')){
                    $auth->revokeAll($id_user);
                    foreach (Yii::$app->request->post('roles') as $role){
                        $roleRbac = $auth->getRole($role);
                        $auth->assign($roleRbac, $id_user);
                    }
                }
                $user->save();
            }

            if ($postData['organizations']){
                $user->unlinkAll('organizations',true);

                foreach ($postData['organizations'] as $idOrganization){
                    $organization_object = Organizations::findOne($idOrganization);
                    if(!$organization_object) ResponseError::returnSingleError(404, "Struttura non trovata");
                    $user->link('organizations', $organization_object);
                }
            }

            $dbTrans->commit();
            return $user;

        } catch (\Exception $e) {
            Yii::error($e);
            $dbTrans->rollBack();

            throw $e;
        }
    }

    public function actionDelete( $id ) {
        $user = user::findone($id);
        if(!$user) ResponseError::returnSingleError( 404, "Utente non trovato");

        if(!$user->delete()) responseerror::returnmultipleerrors( 422, $user->geterrors());

        return ['message' => 'ok'];
    }

    public function actionChangePassword($id)
    {
        $user = User::findOne($id);
        if(!$user) ResponseError::returnSingleError(404, "Utente non trovato");

        $oldPassword = Yii::$app->request->post('old_password');
        $newPassword = Yii::$app->request->post('new_password');
        $repeatPassword = Yii::$app->request->post('repeat_password');

        $model = new LoginForm();
        $params= ['username' => $user->username,'password' => $oldPassword];
        if(!($model->load($params, '') && $model->login())) ResponseError::returnSingleError(400, "La vecchia password non coincide!");

        $isValidPassword = $user->isValidPassword($newPassword);
        if(!$isValidPassword) ResponseError::returnSingleError(400, "La password deve contenere più di 8 caratteri con lettere maiuscole minuscole numeri e caratteri speciali");

        if($newPassword != $repeatPassword) ResponseError::returnSingleError(400, "Le password devono coincidere");
//            $lasts = UserLastPasswords::find()
//                ->where(['id_user' => $user->id])
//                ->andWhere([
//                    '=',
//                    "pgp_sym_decrypt(CAST( password AS bytea ), '".
//                    Yii::$app->params['encryption']['key']."'
//                    )",
//                    @Yii::$app->request->post('UserChangePassword')['new_password']
//                ])
//                ->limit(5)
//                ->orderBy(['created_at' => SORT_DESC])
//                ->count();
//
//            if($lasts > 0) ResponseError::returnSingleError( 422, "Hai già usato questa password, scegline un'altra" );
//            $storico = new UserLastPasswords;
//            $storico->id_user = $user->id;
//            $storico->password = @Yii::$app->request->post('UserChangePassword')['old_password'];
//            $storico->save();

        $user->is_temp_password = 0;
        $user->expired_password = date("Y-m-d", strtotime("+1 month"));

        $user->setPassword($newPassword);
        $user->generateAuthKey();
        if (!$user->save()) ResponseError::returnMultipleErrors(422, $user->getErrors() );

        return $user;
    }

    public function actionResetPassword()
    {
        $conn = \Yii::$app->db;
        $dbTrans = $conn->beginTransaction();

        try {
            $postData = Yii::$app->request->post();
            $user = User::find()->where(['email' => $postData['email']])->one();
            if(!$user) ResponseError::returnSingleError(404, "Utente non trovato");

            $randomPassword= $user->setRandomPassword();
            $user->setPassword($randomPassword);
            $user->expired_password=date("Y-m-d", strtotime("+1 month"));
            $user->is_temp_password = 1;

            if(!$user->save()) ResponseError::returnMultipleErrors( 422, $user->getErrors());

            $sent = SendMail::send(
                Yii::$app->params['adminEmail'],
                $user->email,
                'Reset Password',
                [],
                [
                    'use_layout'=>true,
                    'theme' => [
                        'html'=>'passwordResetToken-html',
                        'text'=>'passwordResetToken-text'
                    ],
                    'theme_vars' => [
                        'user' => $user,
                        'username' => $user->username,
                        'email'=>$user->email,
                        'password'=>$randomPassword,
                    ]
                ]
            );

            if(!$sent) ResponseError::returnSingleError( 500, "Errore invio mail");

            $dbTrans->commit();
            return $user;

        } catch (\Exception $e) {
            Yii::error($e);
            $dbTrans->rollBack();

            throw $e;
        }

        return $user;
    }

    /**
     * Il metodo restituisce il profilo utente
     *
     **
     * @SWG\Get(path="/user/profile",
     *     tags={"user"},
     *     description="Restituisce il profilo utente. La proprietà role contiene il Ruolo; permissions i permessi; wards i reparti cui ha accesso (che serve anche per la costruzione della voce di menu Reparti).",
     *
     *     @SWG\Response(
     *         response = 200,
     *         description = " success"
     *     )
     * )
     *
     */
    public function actionProfile( )
    {
        return User::findOne( Yii::$app->user->identity->id );
    }

    public function actionAddOrganization($idUser, $idOrganization){
        $user = User::find($idUser)->one();
        if(!$user) ResponseError::returnSingleError(404, "Utente non trovato");

        $organization = Organizations::find($idOrganization)->one();
        if(!$organization) ResponseError::returnSingleError(404, "Struttura non trovata");

        $conUserOrganization = ConUserOrganization::find()->where(['id_utente' => $user->getId(), 'id_organization' => $organization->id]);
        if($conUserOrganization) ResponseError::returnSingleError(404, "Utente già associato");

        $user->link('organizations', $organization);

        return $user;
    }

    public function actionCreatePerson($id){
        $conn = \Yii::$app->db;
        $dbTrans = $conn->beginTransaction();

        try {
            $user = User::findOne($id);

            $postData = Yii::$app->request->post();
            $person = Persons::find()->where(['fiscal_code' => $postData['person']['fiscal_code']])->one();
            if(!$person){
                $person = new Persons;
                $person->load(['Persons' => $postData['person']]);
                $person->gender = !empty($postData['gender']) ? $postData['gender'] : 'M';
                $person->birth_city = !empty($postData['birth_city']) ? $postData['birth_city'] : 4851;
                $person->birth_nation = !empty($postData['birth_nation']) ? $postData['birth_nation'] : 100;

                if(!$person->save()) ResponseError::returnMultipleErrors( 422, $person->getErrors());
            }

            $user->id_person = $person ? $person->id : null;
            if(!$user->save()) ResponseError::returnMultipleErrors( 422, $user->getErrors());

            $id_user = Yii::$app->request->post('id_user');
            if($id_user && array_key_exists('role',Yii::$app->request->post())){
                $user = user::findOne($id_user);
                $auth = Yii::$app->authManager;
                if($user->role->name != Yii::$app->request->post('role')){
                    $role = $auth->getRole(Yii::$app->request->post('role'));
                    $auth->assign($role, Yii::$app->request->post('id_user'));
                }
            }

            if (array_key_exists('organizations',$postData)){
                $user->unlinkAll('organizations',true);

                foreach ($postData['organizations'] as $idOrganization){
                    $organization_object = Organizations::findOne($idOrganization);
                    if(!$organization_object) ResponseError::returnSingleError(404, "Struttura non trovata");
                    $user->link('organizations', $organization_object);
                }
            }

            $dbTrans->commit();
            return $user;

        } catch (\Exception $e) {
            Yii::error($e);
            $dbTrans->rollBack();

            throw $e;
        }
    }














    /**
     * Abilita ip per utente
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function actionAllowIp( $id )
    {
        $block_ip = new UserAllowedIps;
        $block_ip->id_user = $id;
        $block_ip->ip = Yii::$app->request->post('UserAllowedIps')['ip'];
        $block_ip->status = 0;
        if(!$block_ip->save()) ResponseError::returnMultipleErrors( 422, $block_ip->getErrors() );

        return ['message'=>'ok', 'UserAllowedIps' => $block_ip];
    }

    /**
     * Blocca ip per utente
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function actionBlockIp( $id )
    {
        $block_ip = new UserAllowedIps;
        $block_ip->id_user = $id;
        $block_ip->ip = Yii::$app->request->post('UserAllowedIps')['ip'];
        $block_ip->status = 1;
        if(!$block_ip->save()) ResponseError::returnMultipleErrors( 422, $block_ip->getErrors() );

        return ['message'=>'ok', 'UserAllowedIps' => $block_ip];
    }

    /**
     * @todo test
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function actionRemoveBlockIp( $id, $id_ip )
    {
        $block_ip = UserAllowedIps::findOne($id_ip);
        if(!$block_ip->delete()) ResponseError::returnMultipleErrors( 422, $block_ip->getErrors() );

        return ['message'=>'ok'];
    }

    /**
     * @todo test
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function actionChangeUserPassword( $id )
    {

        $user = User::findOne( $id );
        $user->setPassword(Yii::$app->request->post('new_pwd'));
        $user->generateAuthKey();
        $user->is_temp_password = 1;
        if(!$user->save()) ResponseError::returnMultipleErrors( 422, $user->getErrors());

        return $user;
    }
}
