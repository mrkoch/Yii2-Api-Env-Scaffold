<?php
namespace  common\traits;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

trait Searchable {
	private $search_params = [];
	private $reservedParams = ['sort', 'q', 'expand', 'fields'];

	private $query_with = array();
	private $query_attributes = array();
	private $query_select = array();

	public $return_model;

	/**
	 * Prendi attributi da cercare dalla proprietà query_attrs del model se esiste
	 * @return void
	 */
	private function getQueryAttributes()
	{
		$this->query_attributes = ($this->query_attrs) ? $this->query_attrs : [];
	}

	/**
	 * Prendi attributi da cercare dalla proprietà query_attrs del model se esiste
	 * @return void
	 */
	private function getSelectAttributes()
	{
		$this->query_select = (property_exists( $this, 'defaultSelect' ) ) ? $this->defaultSelect : [];
	}

	/**
	 * Prendi attributi dalla proprietà query_attrs del model se esiste
	 * @return void
	 */
	private function getWiths()
	{
		$default = ( property_exists( $this, 'defaultWiths' ) ) ? $this->defaultWiths : [];
		$query_with_string = isset($this->search_params['with']) ? explode(",", $this->search_params['with']) : [];

		$this->query_with = array_merge($default, $query_with_string);

		$arr_expand = [];
		foreach ($this->query_with as $with) {
			//Yii::error($with);
			if(preg_match("/ as /i", $with)){
				//Yii::error("match");
				$arr_expand[] = explode(" ", $with)[0];
			} else {
				$arr_expand[] = $with;
			}
		}

		$arr_expand = array_unique($arr_expand);
		$_GET['expand'] = implode(",", $arr_expand);
	}

	/**
	 * Prendi attributi dalla proprietà query_attrs del model se esiste
	 * @return void
	 */
	private function setGroupBy()
	{
		if ( property_exists( $this, 'groupBy' ) ) $this->return_model->groupBy($this->groupBy);
	}

	private function setWiths()
	{	// joinWith
		if(count($this->query_with) > 0) $this->return_model->joinWith( $this->query_with );
	}

	private function setSelects()
	{

		if(count($this->query_select) > 0) :
			foreach ($this->query_select as $select) $this->return_model->select($select);
		endif;
	}

	private function setQueryAttributes()
	{
		if (!empty($this->search_params)) :

            foreach ($this->search_params as $key => $value) :

                if(!is_scalar($key) or !is_scalar($value))  throw new BadRequestHttpException('Bad Request');

                if (!in_array(strtolower($key), $this->reservedParams)
                    && ArrayHelper::keyExists($key, $this->query_attributes, false)) $this->replaceSearchAttr($key, $value);

                if($key == 'sort')  $this->setSort($key, $value);

            endforeach;
        endif;
	}

	private function replaceSearchAttr($key, $value) {
		switch($this->query_attributes[$key][0]):
			case 'notexist':
				$this->makeSubQuery($key, $value);
			break;
			case 'exist':
				$this->makeSubQueryExists($key, $value);
			    break;
			case 'andFilterOr':
				$this->andFilterWhereQuery($key, $value);
			    break;
			case 'encrypt':
				$this->encryptQuery($key, $value);
			    break;
			case 'likestart':
				$this->likestartQuery($key, $value);
			    break;
			case 'hstore':
				$this->hstoreQuery($key, $value);
			    break;
            case 'json':
                $this->jsonQuery($key, $value);
                break;
            case 'php_func':
                $this->phpFunction($key, $value);
                break;
            case 'in':
                $this->inQuery($key, $value);
                break;
            case 'in_and_null':
                $this->inQueryAndNull($key, $value);
                break;
            case 'method_static':
                $this->methodStatic($key, $value);
                break;
			default:
				$this->normalQuery($key, $value);
			break;
		endswitch;
	}

	private function normalQuery($key, $value) {
		$this->return_model->andWhere([$this->query_attributes[$key][0], $this->query_attributes[$key][1], $value]);
	}

	private function phpFunction($key, $value) {
      $q_val = call_user_func_array( $this->query_attributes[$key][1], [$value]);
      $this->return_model->andWhere([$this->query_attributes[$key][2], $this->query_attributes[$key][3], $q_val]);
    }

    private function inQuery($key, $value) {
        $this->return_model->andWhere(['in', $this->query_attributes[$key][1], explode(",", $value)]);
    }

    private function inQueryAndNull($key, $value) {
        $values = explode(",", $value);
        if(in_array('NULL', $values)){
            $this->return_model->andWhere(['or',
                ['in', $this->query_attributes[$key][1], array_diff($values,['NULL'])],
                ['is', $this->query_attributes[$key][1], new \yii\db\Expression('null')],
            ]);
        }else{
            $this->return_model->andWhere(['in', $this->query_attributes[$key][1], $values]);
        }
    }

   /**
    * Usato per la chiamata a un metodo statico di callback per l'elaborazione del valore.
    * Lo schema del metodo dev'essere il seguente:
    * public static function nome_metodo($par1,$par2,$par3,...) {...}
    * dove $par1 è il valore ricevuto dal client.
    * In $query_attrs del model la definizione deve contenere i seguenti parametri:
    * 1) method_static. Parametro fisso per la chiamata a questa funzione
    * 2) nomeClasse::nomeMetodo. La firma della classe e metodo completa del namespace
    * 3) nome del campo. Il campo su cui deve impostare la condizione
    * 4) operatore di confronto
    * 5) altri parametri per la callback. I parametri devono essere passati come array e saranno mappati ai parametri
    *    della callback nello stesso ordine (il primo parametro è sempre il valore ricevuto dal client)
    * Esempio di definizione nel model:
    * public $query_attrs = [
    *    ...
    *    'nome' => ['method_static', '\common\models\hospitalization\Monitor::completeDatetime', 'monitor.detected', '>=', ['START']],
    *    ...
    * ]
    * @param array $key
    * @param string|mixed $value
    */
   private function methodStatic($key, $value) {
      $params = [$value];
      if (count($this->query_attributes[$key]) == 5) {
         $params = $this->query_attributes[$key][4];
         array_unshift($params, $value);
      }
      $q_val = call_user_func_array( $this->query_attributes[$key][1], $params);
      $this->return_model->andWhere([$this->query_attributes[$key][3], $this->query_attributes[$key][2], $q_val]);
   }

   private function encryptQuery($key, $value) {

		$this->return_model->andWhere([
			$this->query_attributes[$key][1],
			"pgp_sym_decrypt( CAST( ".$this->query_attributes[$key][2]." AS bytea ), '".
			Yii::$app->params['encryption']['key']."'
			)",
			$value
		]);
	}

	private function likestartQuery($key, $value) {
		$this->return_model->andWhere([
			'ilike',
			$this->query_attributes[$key][1],
			$value."%",
			false]
		);
	}

    private function jsonQuery($key, $value) {

        $valid_operators = ['ilike','in','not in'];

        $values = explode(",", $value);

        $field = $this->query_attributes[$key][1];
        $operator = $this->query_attributes[$key][2];
        $json_field = $this->query_attributes[$key][3];

        if(in_array($operator, $valid_operators)){

            $splittedValues = explode(",", $value);
            $formattedValues = "'" . implode("', '", $splittedValues) ."'";

            $this->return_model
                ->andWhere(" $field ->> :meta_key_$key $operator ($formattedValues)")
                ->addParams([
                    //":meta_val_$key" => $numbers,
                    ":meta_key_$key" => $json_field
                ]);
        }
    }


	private function hstoreQuery($key, $value) {
		/**
		 * Splitto valori inviati
		 * separatore da usare |||
		 *
		 * esmpio  ?meta=Campo|||Altro|||ilike
		 */
		try {
			$valid_operators = ['=','!=','ilike','in','not in'];

			$keys = explode(",", $value);

			foreach ($keys as $val) {
				$values = explode("|||", $val);

				$key = $this->query_attributes[$key][1];

				$this->return_model
				->andWhere(" $key -> :meta_key = :meta_val")
				->addParams([
					':meta_val' => $values[1],
					':meta_key' => $values[0]
	            ]);
			}

		} catch(\Exception $e) {
			Yii::error($e);
			Yii::error("Errore split parametri query hstore ".$value);
		}
	}

	private function andFilterWhereQuery($key, $value) {
		$params = explode(",", $value);
		if(count($params) > 0):
            $ors = ['or'];
            foreach ($params as $param) $ors[] = [$this->query_attributes[$key][1] => $param];

            $this->return_model->andFilterWhere($ors);
        endif;
	}

	private function makeSubQuery($key, $value) {
		$subquery = $this::find();

    	if(isset($this->query_attributes[$key]['with'])) $subquery->joinWith($this->query_attributes[$key]['with']);
    	$subquery->where(['=', $this->query_attributes[$key][1], $value]);

    	if(isset($this->query_attributes[$key]['where'])) $subquery->andWhere($this->query_attributes[$key]['where']);
    	if(isset($this->query_attributes[$key]['select'])) $subquery->select($this->query_attributes[$key]['select']);

    	$this->return_model->andWhere(['not in', $this->query_attributes[$key]['not_in'], $subquery]);
	}

	private function makeSubQueryExists($key, $value) {
		$subquery = $this::find();

    	if(isset($this->query_attributes[$key]['with'])) $subquery->joinWith($this->query_attributes[$key]['with']);
    	$subquery->where(['=', $this->query_attributes[$key][1], $value]);

    	if(isset($this->query_attributes[$key]['where'])) $subquery->andWhere($this->query_attributes[$key]['where']);
    	if(isset($this->query_attributes[$key]['select'])) $subquery->select($this->query_attributes[$key]['select']);

    	$this->return_model->andWhere(['in', $this->query_attributes[$key]['in'], $subquery]);
	}

	private function setSort($key, $value) {
		$val = str_replace("-", "", $value);
		if(!property_exists( $this, 'sortMap' ) || !isset($this->sortMap[$val])) :
			$this->return_model->orderBy([$val =>(substr($value, 0, 1) == '-') ? SORT_DESC : SORT_ASC]);
		else:
			if(is_array($this->sortMap[$val])) :
				if($this->sortMap[$val][0] == 'encrypt') :
					$this->return_model->orderBy(["AES_DECRYPT(FROM_BASE64(".$this->sortMap[$val][1]."),'".Yii::$app->params['encryption']['key']."')" => (substr($value, 0, 1) == '-') ? SORT_DESC : SORT_ASC]);
				else:
					$this->return_model->orderBy([$this->sortMap[$val] =>(substr($value, 0, 1) == '-') ? SORT_DESC : SORT_ASC]);
				endif;

			else :
				$this->return_model->orderBy([$this->sortMap[$val] =>(substr($value, 0, 1) == '-') ? SORT_DESC : SORT_ASC]);
			endif;

    	endif;
	}



	public function search()
	{
		// set all get params as this->search_params
		$this->search_params = Yii::$app->request->get();

		$this->getWiths();
		$this->getQueryAttributes();
		$this->getSelectAttributes();

		$this->return_model = $this::find();

		$this->setSelects();
		$this->setWiths();
		$this->setQueryAttributes();
		$this->setGroupBy();

		return $this->return_model;

	}

}
