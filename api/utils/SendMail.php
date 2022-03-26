<?php
namespace api\utils;

use Yii;

use common\models\app\AppLog;

class SendMail
{

	public static function send($from = "", $to = "", $subject = "", $files = [], $conf = [])
    {
        
        if(isset($conf['use_layout'])) :
            $send_mail = Yii::$app->mailer->compose($conf['theme'], $conf['theme_vars'])
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject);
        else:
            $send_mail = Yii::$app->mailer->compose()
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->setTextBody($conf['text'])
            ->setHtmlBody($conf['html']);
        endif;

        if($files && count($files) > 0) :
            foreach ($files as $file_path) :
                $send_mail->attach($file_path);
            endforeach;
        endif;
       
        $sent = $send_mail->send();

        $log = new AppLog;
        $log->load(['AppLog'=>[
            'model'=> 'SendMail',
            'action' => 0,
            'model_id' => null,
            'id_user' => @Yii::$app->user->identity->id,
            'model_pre' => json_encode([
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'files' => $files,
                'conf' => $conf
            ]),
            'model_post' => ($sent) ? 'Invio riuscito' : 'Invio fallito'
        ]]);
        $log->save();

        return $sent;

    }

    
}