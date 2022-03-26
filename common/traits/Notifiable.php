<?php 
namespace common\traits;

use Yii;
use common\utils\SendNotification;

trait Notifiable {
	
	public $avaible_notification_channels = ['email','pec'];

	/**
	 * [notify description]
	 * @param  [type] $channel email, pec
	 * @param  array $data ['subject','files','conf']
	 * @return [type]          [description]
	 */
	public function notify( $channel, $data ) {

		if( !in_array( $channel, $this->avaible_notification_channels ) ) return;

		//$data = $from = "", $to = "", $subject = "", $files = [], $conf = []
		
		$contatti = $this->takeContattiForNotification();
		$deliver_to = [];
		foreach ($contatti as $contatto) {
			if ( !empty($contatto->$channel) ) $deliver_to[] = $contatto->$channel;
		}

		// per poter gestire diversi smtp
		$method = ($channel == 'pec') ? 'sendPec' : 'send';

		foreach ($deliver_to as $destinatario) :

			$sent = call_user_func_array("\common\utils\SendNotification::".$method, [
	            Yii::$app->params['adminEmail'],
	            $destinatario,
	            $data[0],
	            $data[1],
	            $data[2]
	        ]);

	    endforeach;

	}

}