<?php 
namespace api\utils;

use Yii;

class Functions {

    public static function getRandomString( $length ) {

    	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	    
    }

    public static function getLimitPagination( $requestPagination ) {

        $limit = !empty($requestPagination['pageSize']) ? $requestPagination['pageSize'] : 10;
        return $limit;
    }


   /**
    * Genera un UUID v.4 secondo le specifiche RFC 4122
    * Riferimenti: https://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid/59776015#59776015
    *              https://it.wikipedia.org/wiki/Universally_unique_identifier
    *              https://tools.ietf.org/html/rfc4122)
    * @param null $data
    * @return string
    * @throws \Exception
    */
    public static function generateUUIDv4() {
       $data = random_bytes(16);
       $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
       $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
       return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function dbErrorsToString($errors) {
       $res = [];
       foreach ($errors as $fieldname=>$errs) $res[] = $fieldname.": ".join('; ',$errs);
       return join(". \n",$res);
    }

   /**
      PARA: Date Should In YYYY-MM-DD Format
      RESULT FORMAT:
       '%y Year %m Month %d Day %h Hours %i Minute %s Seconds'        =>  1 Year 3 Month 14 Day 11 Hours 49 Minute 36 Seconds
       '%y Year %m Month %d Day'                                    =>  1 Year 3 Month 14 Days
       '%m Month %d Day'                                            =>  3 Month 14 Day
       '%d Day %h Hours'                                            =>  14 Day 11 Hours
       '%d Day'                                                        =>  14 Days
       '%h Hours %i Minute %s Seconds'                                =>  11 Hours 49 Minute 36 Seconds
       '%i Minute %s Seconds'                                        =>  49 Minute 36 Seconds
       '%h Hours                                                    =>  11 Hours
       '%a Days                                                        =>  468 Days
   **/
   public static function dateDifference($date1 , $date2 , $differenceFormat = '%a' )
   {
      $datetime1 = date_create($date1);
      $datetime2 = date_create($date2);
      $interval = date_diff($datetime1, $datetime2);
      return $interval->format($differenceFormat);
   }

   public static function roleTranslate($arrRole,$language)
   {
       foreach ($arrRole as $role){
           $role->name = !empty(Yii::$app->params['roleTraslateIt'][$role->name]) ? Yii::$app->params['roleTraslateIt'][$role->name] : 'No translate';
       }
       return $arrRole;
   }

   /**
    * Function to recursively process a directory and all its subdirectories
    * @param string $dir
    * @return array
    */
   static public function dirTraverse($dir) {
      // check if argument is a valid directory
      if (!is_dir($dir)) { die("Argument '$dir' is not a directory!"); }

      // declare variable to hold file list
      global $fileList;

      // open directory handle
      $dh = opendir($dir) or die ("Cannot open directory '$dir'!");

      // iterate over files in directory
      while (($file = readdir($dh)) !== false)  {
         // filter out "." and ".."
         if ($file != "." && $file != "..") {
            if (is_dir("$dir/$file")) {
               // processa ricorsivamente la subdirectory
               dirTraverse("$dir/$file");
            } else    {
               // memorizza il nome del file
//               $path_parts = pathinfo($file);
//               $fileList[] = $path_parts['filename'];
               $fileList[] = $file;
            }
         }
      }
      return $fileList;
   }

}