<?php
namespace api\modules\v1\controllers\user;

use Yii;
use yii\base\Controller;
use yii\data\ActiveDataProvider;
use yii\data\ActiveDataFilter;

use sizeg\jwt\JwtHttpBearerAuth;
use sizeg\jwt\Jwt;
use Lcobucci\JWT\Signer\Hmac\Sha256;

use common\models\LoginForm;
use common\models\User;

use common\models\user\UserAllowedIps;
use common\models\user\UserLoginLog;

use api\utils\ResponseError;
use api\utils\SendMail;
class ResetController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
            'except' => ['ask-reset', 'reset', 'options']
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
     * @SWG\Post(path="/auth/reset/ask-reset",
     *     tags={"user"},
     *     summary="Reset",
     *     description="Richiesta recupera password",
     *     produces={"application/json"},
     *     @SWG\Parameter(in = "formData", description = "", type = "string", required = true, name = "base_url", default = "http://localhost:3000",),
     *     @SWG\Parameter(in = "formData", description = "", type = "string", required = true, name = "email", default = "info@mailinator.com",),
     *     @SWG\Response(response = 200, description = " success")
     * )
     */
    public function actionAskReset()
    {
        $data = Yii::$app->request->post();

        if(!isset($data['base_url'])) ResponseError::returnSingleError(422, "Errore, base url non trovata");
        if(!isset($data['email'])) ResponseError::returnSingleError(422, "Email obbligatoria");

        $user = User::find()->where(['email'=>$data['email']])->one();
	    if(!$user) ResponseError::returnSingleError(404, "Utente non trovato");

        $user->generatePasswordResetToken();
        $user->save();
        $url = $data['base_url'].$user->password_reset_token;

        $sent = SendMail::send(
            Yii::$app->params['adminEmail'],
            $user->email,
            'Reset password',
            [],
            [
                'use_layout'=>true,
                'theme' => [
                    'html'=>'passwordResetToken-html',
                    'text'=>'passwordResetToken-text'
                ],
                'theme_vars' => [
                    'url'=>$url,
                    'username' => $user->username
                ]
            ]
        );
        if(!$sent) ResponseError::returnSingleError( 500, "Errore invio mail");

        return ['message'=>'ok'];
    }

    /**
     * Conferma il reset della password
     * @return [type] [description]
     */

    /**
     * @SWG\Post(path="/auth/reset/reset",
     *     tags={"user"},
     *     summary="Reset",
     *     description="Richiesta recupera password",
     *     produces={"application/json"},
     *     @SWG\Parameter(in = "formData", description = "", type = "string", required = true, name = "token", default = "H0N3GtM3rhG3bxUg-OP40HWWlLzBsY0J_1558357578",),
     *     @SWG\Parameter(in = "formData", description = "", type = "string", required = true, name = "new_pwd", default = "password",),
     *     @SWG\Parameter(in = "formData", description = "", type = "string", required = true, name = "confirm_password", default = "password",),
     *     @SWG\Response(response = 200, description = " success")
     * )
     */
    public function actionReset()
    {
        $data = Yii::$app->request->post();

        if(!isset($data['token'])) ResponseError::returnSingleError(422, "Token obbligatorio");

        if(!isset($data['new_pwd']) || !isset($data['confirm_password'])) ResponseError::returnSingleError(422, "Password obbligatorie");

        if ($data['new_pwd'] != $data['confirm_password']) ResponseError::returnSingleError(422, "Le password non coincidono");

        if($data['new_pwd'] == '') ResponseError::returnSingleError(422, "Password non valida");

        // validità token 3600 secondi
        $tkn_time = explode("_", $data['token']);
        if(time()-$tkn_time[count($tkn_time)-1] > Yii::$app->params['user.passwordResetTokenExpire']) ResponseError::returnSingleError(422, "Token scaduto");

        $user = User::find()->where(['password_reset_token'=>$data['token']])->one();
        if(!$user) ResponseError::returnSingleError(422, "Token non valido");

        $user->setPassword($data['new_pwd']);
        $user->removePasswordResetToken();
        $user->save();

        return ['message'=>'ok'];

    }


}
