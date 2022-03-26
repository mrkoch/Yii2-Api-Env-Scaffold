<?php
namespace api\modules\v1\controllers\user;

use Yii;
use yii\base\Controller;
use sizeg\jwt\JwtHttpBearerAuth;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use common\models\LoginForm;
use common\models\User;
use common\models\user\UserAllowedIps;
use common\models\user\UserLoginLog;

use api\utils\ResponseError;

class AuthController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
            'except' => ['login', 'options']
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
     * Login
     * @param string $username Username User
     * @param string $password Password User
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        $request = Yii::$app->getRequest();

        if ($model->load($request->getBodyParams(), '') && $model->login()) :
            $request = new yii\web\Request;
            $ip = $request->getUserIP();

            $agent = $request->getUserAgent();

            $signer = new Sha256();

            $user = User::findOne(Yii::$app->user->identity->id);

//            if($user->is_temp_password || $user->expired_password < date("Y-m-d H:i:s")){
//                $token = '';
//            }
//            else{
                $token = Yii::$app->jwt->getBuilder()
                    ->setIssuer(Yii::$app->params['iss']) // Configures the issuer (iss claim)
                    ->setAudience(Yii::$app->params['aud']) // Configures the audience (aud claim)
                    ->setId(Yii::$app->params['tid'], true) // Configures the id (jti claim), replicating as a header item
                    ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
                    ->setNotBefore(time()) // Configures the time before which the token cannot be accepted (nbf claim)
                    ->setExpiration(time() + (3600*24*365)) // Adesso equivale a un anno
                    ->set( 'uid', Yii::$app->user->identity->id ) // Configures a new claim, called "uid"
                    ->set( 'ip', $ip )
                    ->set( 'agent', $agent )
                    ->sign($signer, Yii::$app->params['secret-key'])
                    ->getToken(); // Retrieves the generated token
//            }

            //$token->getToken()->isExpired() per sapere se il token è scaduto
            return [
                'token' => "" . $token,
                'user' => $user->toArray([], ['person', 'roles','wards','permissions']),
//                'is_temp_password' => $user->is_temp_password ? true : false,
//                'is_expired_password' => $user->expired_password < date("Y-m-d H:i:s") ? true : false
            ];

        else:
            ResponseError::returnMultipleErrors( 422, $model->getErrors());
        endif;
    }

    /**
     * Verifica gli indirizzi ip associati all'utente
     * @param  [type] $user_id            [description]
     * @param  [type] $request_ip_address [description]
     * @return [type]                     [description]
     */
    private function verifyAllowedIps( $user_id, $request_ip_address )
    {
        // abilitati
        // solo se > 0 verifico che l'ip sia tra quelli abilitati
        // se sono == 0 sono tutti validi gli ip
        $allowed = UserAllowedIps::find()->where(['id_user' => $user_id])->andWhere([ 'status' => 0 ])->all();
        if ( count($allowed) > 0 ) :
            $can_access = false;
            foreach ($allowed as $all) :
                if ( $all->ip == $request_ip_address ) $can_access = true;
            endforeach;

            if(!$can_access) ResponseError::returnSingleError( 403, "Indirizzo ip bloccato per questo utente" );
        endif;

        // bloccati
        // possono essere usati per bloccare il vecchio gestore delle organizzazioni
        $blocked = UserAllowedIps::find()->where(['id_user' => $user_id])->andWhere([ 'status' => 1 ])->andWhere(['ip'=>$request_ip_address])->count();
        if ( $blocked > 0 ) ResponseError::returnSingleError( 403, "Indirizzo ip bloccato per questo utente" );
    }

    /**
     * Inserisci log dell'utente
     * @param [type] $user_id            [description]
     * @param [type] $request_ip_address [description]
     */
    private function addLog( $user_id, $request_ip_address )
    {
        $log = new UserLoginLog;
        $log->ip = $request_ip_address;
        $log->time = time();
        $log->id_user = $user_id;
        $log->save();
    }


}
