<?php
namespace console\controllers;

use common\models\hospitalization\Organizations;
use Exception;
use phpDocumentor\Reflection\Types\This;
use Yii;
use yii\console\Controller;
use common\models\User;
use common\models\Scadenze;

class UtilsController extends Controller
{
    public $username;
    public $password;
    public $email;
    public $role;
    public $ward;

    public function options($actionID)
    {
        return ['username','password','email','role','ward'];
    }
    
    public function optionAliases()
    {
        return [
            'u' => 'username', 'p'=>'password', 'e' => 'email', 'r' => 'role', 'w'=> 'ward'
        ];
    }

    /**
     * Inserisci utente
     * ./yii utils/add-user -u="direttoresanitario" -e="direttoresanitario@email.it" -p="Password1"
     *
     * @return [type] [description]
     */
    public function actionAddUser()
    {
        $user = new User;
        $user->email = $this->email;
        $user->username = $this->username;
        $user->status = User::STATUS_ACTIVE;

        
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->save();

        echo "Inserito\n";
    }

    /**
     * Imposta ruolo a utente
     * ./yii utils/add-role-to-user -u="direttoresanitario" -r="HealtDirector"
     *
     */
    public function actionAddRoleToUser()
    {
        
        $user = User::find()->where(['username'=>$this->username])->one();
        if(!$user) :
            echo "Utente non trovato\n";
            return;
        endif;

        $auth = Yii::$app->authManager;
        $role = $auth->getRole($this->role);
        if($role) $auth->assign($role, $user->id);

        echo "Ruolo aggiunto\n";

    }

    /**
     * Modifica password utente
     *
     * ./yii utils/change-password -u="medico" -p="Password1!"
     * @return void
     */
    public function actionChangePassword()
    {
        $auth = Yii::$app->authManager;

        $user = User::find()->where(['username'=>$this->username])->one();

        if(!$user) :
            echo "Utente non trovato\n";
            return;
        endif;

        $user->setPassword($this->password);
        $user->generateAuthKey();

        if(!$user->save()) :
            print_r($user->getErrors());
        endif;

        echo "Aggiornato\n";
    }
    

   /**
    * Associa il reparto all'utente
    * ./yii utils/add-ward-to-user -u="maurizio" -w=8
    *
    * @return [type] [description]
    */
    public function actionAddWardToUser()
    {
        $user = User::find()->with('organizations')->where(['username'=>$this->username])->one();
        if (!$user) {
          echo "Utente non trovato\n";
          return;
        }

        $id_orgs = [];
        foreach ($user->organizations as $org){
           $id_orgs[] = $org->id;
        }

        if(!in_array($this->ward, $id_orgs)){
            $organization = Organizations::findOne($this->ward);
            if(!empty($organization)){
                $user->link('organizations', $organization);
            }
            echo "Aggiornato!\n";
        }else{
            echo "Reparto già inserito\n";
        }
    }
}
