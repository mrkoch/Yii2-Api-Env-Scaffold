<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{

   /**
    * List roles
    * @var array
    */
   private $roles = [
        'SuperAdmin' => ['description' => 'Super Admin'],
        'Admin' => ['description' => 'Admin'],
   ];

   /**
    * List permissions linked to roles
    * @var array
    */
    private $permissions = [

        // Profile permissions
        'viewProfile' => ['description' => "View profile", 'roles' => ['Admin'], 'rule' => null],
        'editProfile' => ['description' => "Edit profile", 'roles' => ['Admin'], 'rule' => null],
        'deleteProfile' => ['description' => "Delete profile", 'roles' => ['Admin'], 'rule' => null],
    ];

   /**
    * Create roles/permissions map
    * if mantain = 1 update
    * if mantain = 0 create
    * ./yii rbac/create-map
    * @return [type] [description]
    */
   public function actionCreateMap($mantain = 0)
   {
      $auth = Yii::$app->authManager;

      // Update/Create roles
      foreach ($this->roles as $rolename => $data) {
         $rl = $auth->getRole($rolename);
         if ($mantain == 0 && $rl) {
            $auth->removeChildren($rl);
         }
         if (!$rl) {
            $rl = $auth->createRole($rolename);
            if ($data['description']) $rl->description = $data['description'];
            $auth->add($rl);
            echo "Add role " . $rolename . "\n";
         }
      }

      // Insert permissions, rule and link permission to role
      foreach ($this->permissions as $permName => $data) {
         if (!$pm = $auth->getPermission($permName)) {
            $pm = $auth->createPermission($permName);
            $auth->add($pm);
            echo "Add permission $permName \n";
         }
         if ($data['description']) $pm->description = $data['description'];
         if (isset($data['rule'])) {
            $ruleName = $data['rule'];
            if (!$rule = $auth->getRule($ruleName)) {
               $classname = "\\common\\rbac\\rules\\$ruleName";
               $rule = new $classname;
               $auth->add($rule);
               echo "Add rule $ruleName\n";
            }
            $pm->ruleName = $rule->name;
         } else $pm->ruleName = null;
         $auth->update($permName, $pm);
         echo "Update permission $permName \n";
         if (!empty($data['roles'])) {
            foreach ($data['roles'] as $rolename) {
               $rl = $auth->getRole($rolename);
               $auth->addChild($rl, $pm);
               echo "Link permission $permName to role $rolename\n";
            }
         }
      }
   }

}