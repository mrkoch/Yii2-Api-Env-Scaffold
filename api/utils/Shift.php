<?php
namespace api\utils;

use Yii;
use yii\helpers\ArrayHelper;


class Shift
{

   /**
    * Restituisce i turni definiti come parametro shifts in config/params se esiste, altrimenti quelli definiti in questo
    * metodo.
    * Il parametro shifts dev'essere definito con la seguente struttura:
    * [
    *    'M'=> ['name'=>'Mattina',   'start'=>'07:00',   'end'=>'13:30'],
    *    'P'=> ['name'=>'Pomeriggio','start'=>'13:30',   'end'=>'20:00'],
    *    'N'=> ['name'=>'Notte',     'start'=>'20:00',   'end'=>'07:00']
    * ]
    * Ogni orario deve contenere ora e minuti con i due punti separatori nel formato HH:ii
    */
   static public function getAll() {
      return ArrayHelper::getValue(Yii::$app->params,'shifts') ?: [
        'M'=> ['name'=>'Mattina',   'start'=>'07:00',   'end'=>'13:30'],
        'P'=> ['name'=>'Pomeriggio','start'=>'13:30',   'end'=>'20:00'],
        'N'=> ['name'=>'Notte',     'start'=>'20:00',   'end'=>'07:00']
      ];
   }

   /**
    * Ritorna il turno corrispondente alla chiave
    * @param string(1) $key
    * @return array|null
    */
   static public function getOne($key) {
      $shifts = self::getAll();
      return isset($shifts[$key]) ? $shifts[$key] : null;
   }


   /**
    * Utility che serve a splittare su due fasce orarie il turno notturno che è a cavallo della mezzanotte.
    * Questa funzione serve ai metodi che devono calcolare in quale turno cade una certa data e ora
    */
   static private function splitShiftsNightBand() {
      $shifts = self::getAll();
      $shifts['N1'] = array_merge($shifts['N'], ['end'=>'23.59']);
      $shifts['N2'] = array_merge($shifts['N'], ['start'=>'00.00']);
      unset($shifts['N']);
      return $shifts;
   }

   /**
    * Utility che serve a splittare l'intervallo indicato dai due datetime in fasce orarie. Nel caso in cui l'intervallo
    * è a cavallo della mezzanotte restituisce una doppia fascia oraria in modo da essere comparabile con gli shifts.
    * Questa funzione serve ai metodi che devono calcolare in quale turno cade una certa data e ora
    */
   static private function splitIntervalInBands($datetime1,$datetime2) {
      $hours = [
        ['s'=>(float)date('H.i', strtotime($datetime1)), 'e'=>(float)date('H.i', strtotime($datetime2))]
      ];
      if ($hours[0]['s'] > $hours[0]['e']) {
         $hours[1] = ['s'=>0.0, 'e'=>$hours[0]['e']];
         $hours[0]['e'] = 23.59;
      }
      return $hours;
   }

   /**
    * Individua il turno di un datetime e lo restituisce nella forma richiesta dall'info oppure per default l'intero
    * object turno
    *
    * @param string $datetime Formato ISO: Y-m-d H:i
    * @param string $info Valori: id/name
    * @return string
    */
   static public function getOneByHour($datetime,$info='name') {
      $shifts = self::splitShiftsNightBand();
      $hour = (float)date('H.i', strtotime($datetime));
      foreach ($shifts as $key=> $shift) {
         $start = (float)str_replace(':','.',$shift['start']);
         $end = (float)str_replace(':','.',$shift['end']);
         if ($start <= $hour && $hour <= $end) break;
      }
      $key = substr($key,0,1);
      $shift = self::getOne($key);
      $shift['id'] = $key;
      switch (strtoupper($info)) {
         case 'ID'    : return $key; break;
         case 'NAME'  : return $shift['name']; break;
         default : return $shift;
      }
   }

   /**
    * Restituisce il range dataora in base alla data indicata e al turno.
    * @param string $date Y-m-d
    * @param char(1) $idshift Valori: M/P/N
    * @return array[datetime,datetime]
    */
   static public function getIntervalByDateAndShift($date,$idshift) {
      $shift = self::getOne($idshift);
//      if ($idshift != 'N') return ["$date {$shift['start']}",date('Y-m-d H:i', strtotime("$date {$shift['end']} -1 minutes"))];
      if ($idshift != 'N') return ["$date {$shift['start']}","$date {$shift['end']}"];
      $shifts = self::splitShiftsNightBand();
      $date2 = date('Y-m-d', strtotime("$date +1 day"));
//      return ["$date {$shifts['N1']['start']}",date('Y-m-d H:i', strtotime("$date2 {$shifts['N2']['end']} -1 minutes"))];
      return ["$date {$shifts['N1']['start']}","$date2 {$shifts['N2']['end']}"];
   }

   /**
    * Restituisce i turni compresi nell'intervallo indicato.
    * I casi possibili di sovrapposizione sono 16 (vedi https://codereview.stackexchange.com/a/45804/228145), di cui
    * devono essere esclusi i 2 casi indicati nel post come 6 e 7, cioè quando gli estremi dei due intervalli si escludono a vicenda, e
    * in aggiunta i casi in cui gli estremo opposti di turno e intervallo coincidono (questo per evitare
    * che un'attività che inizia al termine del turno venga assegnata turno uscente e che un'attività che doveva svolgersi
    * nel turno precedente venga assegnata al turno entrante soltanto perché coincide con la fine attività): quindi
    * nel ciclo vengono controllati soltanto questi 4 casi per essere scartati, mentre tutti gli altri vengono accettati.
    *
    * @param string $datetime1 String in formato ISO (Y-m-d H:i)
    * @param string $datetime2 String in formato ISO (Y-m-d H:i)
    * @param string|null $info L'informazione da ritornare. Valori possibili: null (ritorna l'object) | id | name
    * @return array
    */
   static public function getByInterval($datetime1,$datetime2,$info=null) {
      $shifts = self::splitShiftsNightBand();
      $intervals = self::splitIntervalInBands($datetime1,$datetime2);
      $idsShift = [];
      foreach ($shifts as $key=> $shift) {
         $start = (float)str_replace(':','.',$shift['start']);
         $end = (float)str_replace(':','.',$shift['end']);
         foreach ($intervals as $hours) {
            if ($hours['e'] <= $start || $end <= $hours['s']) continue;
            $idsShift[] = substr($key,0,1);
         }
      }
      $idsShift = array_unique($idsShift);
      $shifts = self::getAll();
      $res = [];
      foreach ($shifts as $key=> $shift) {
         if (!in_array($key, $idsShift)) continue;
         $shift['id'] = $key;
         switch (strtoupper($info)) {
            case 'ID'    : $res[] = $shift['id']; break;
            case 'NAME'  : $res[] = $shift['name']; break;
            default      : $res[] = $shift;
         }
      }
      return $res;
   }

}