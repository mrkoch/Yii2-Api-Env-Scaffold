<?php
namespace console\controllers;

use common\models\locations\LocCity;
use Exception;
use Yii;
use yii\console\Controller;


use ruskid\csvimporter\CSVImporter;
use ruskid\csvimporter\CSVReader;
use ruskid\csvimporter\MultipleImportStrategy;


class InstallerController extends Controller
{


   /**
    * Init App and insert required data
    *
    * ./yii installer/init-app
    *
    * @return void
    */
   public function actionInitApp()
   {
      $this->actionLoadLocations();
   }


    /**
     * Insert locations
     * ./yii installer/load-locations
     *
     * @return void
     */
    public function actionLoadLocations()
    {
        $this->actionLoadContinents();
        $this->actionLoadNations();
        $this->actionLoadRegions();
        $this->actionLoadProvinces();
        $this->actionLoadCities();
    }

   /**
     * Insert Continents
     */
    private function actionLoadContinents()
    {
        $path = Yii::getAlias('@console');
        $importer = new CSVImporter;
        $importer->setData(new CSVReader([
            'filename' => $path.'/data/locations/loc_continent.csv',
            'fgetcsvOptions' => [
                'delimiter' => ","
            ]
        ]));
        $tableName = 'loc_continent';
        $config = [
            [
                'attribute' => 'id',
                'value' => function($line) {
                    return $line[0];
                },
                'unique' => true,
            ],
            [
                'attribute' => 'name',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[1]);
                },
                'unique' => true,
            ],
            [
                'attribute' => 'name_en',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[2]);
                },
                'unique' => true,
            ],
        ];
        $importer->import(new MultipleImportStrategy([
            'tableName' => $tableName,
            'configs' => $config,
        ]));
    }


    /**
     * Insert nations
     */
    public function actionLoadNations()
    {
       echo "    > load nations ... ";
        $path = Yii::getAlias('@console');
        $importer = new CSVImporter;
        $importer->setData(new CSVReader([
            'filename' => $path.'/data/locations/loc_nation.csv',
            'fgetcsvOptions' => [
                'delimiter' => ","
            ]
        ]));
        $tableName = 'loc_nation';
        $config = [
            [
                'attribute' => 'id',
                'value' => function($line) {
                    return $line[0];
                },
                'unique' => true,
            ],
            [
                'attribute' => 'idcontinent',
                'value' => function($line) {
                    return $line[1];
                },
                'unique' => false,
            ],
            [
                'attribute' => 'idarea',
                'value' => function($line) {
                    return 0;
                },
                'unique' => true,
            ],
            [
                'attribute' => 'abbreviation',
                'value' => function($line) {
                    return $line[3];
                },
                'unique' => true,
            ],
            [
                'attribute' => 'name',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[4]);
                },
                'unique' => true,
            ],
            [
                'attribute' => 'name_en',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[5]);
                },
                'unique' => true,
            ],
        ];
        $importer->import(new MultipleImportStrategy([
            'tableName' => $tableName,
            'configs' => $config,
        ]));
       echo "done\n";
    }

    /**
     * Insert Regions
     */
    public function actionLoadRegions()
    {
       echo "    > load regions ... ";
        $path = Yii::getAlias('@console');
        $importer = new CSVImporter;
        $importer->setData(new CSVReader([
            'filename' => $path.'/data/locations/loc_region.csv',
            'fgetcsvOptions' => [
                'delimiter' => ","
            ]
        ]));
        $tableName = 'loc_region';
        $config = [
            [
                'attribute' => 'id',
                'value' => function($line) {
                    return $line[0];
                },
                'unique' => true,
            ],
            [
                'attribute' => 'region',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[1]);
                },
                'unique' => true,
            ]
        ];
        $importer->import(new MultipleImportStrategy([
            'tableName' => $tableName,
            'configs' => $config,
        ]));
       echo "done\n";
    }

    /**
     * Insert provinces
     */
    public function actionLoadProvinces()
    {
       echo "    > load provinces ... ";
        $path = Yii::getAlias('@console');
        $importer = new CSVImporter;
        $importer->setData(new CSVReader([
            'filename' => $path.'/data/locations/loc_province.csv',
            'fgetcsvOptions' => [
                'delimiter' => ","
            ]
        ]));
        $tableName = 'loc_province';
        $config = [
            [
                'attribute' => 'id',
                'value' => function($line) {
                    return $line[0];
                },
                'unique' => true,
            ],
            [
                'attribute' => 'id_region',
                'value' => function($line) {
                    return $line[1];
                },
                'unique' => false,
            ],
            [
                'attribute' => 'province',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[2]);
                },
                'unique' => true,
            ],
            [
                'attribute' => 'abbreviation',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[3]);
                },
                'unique' => true,
            ],
            [
                'attribute' => 'coddivision',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[4]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'codnuts1',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[5]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'geographical_area',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[6]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'codnuts2',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[7]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'region',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[8]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'codmetropolis',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[9]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'codnuts3',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[10]);
                },
                'unique' => false,
            ]
        ];
        $importer->import(new MultipleImportStrategy([
            'tableName' => $tableName,
            'configs' => $config,
        ]));
       echo "done\n";
    }

    /**
     * Insert cities
     */
    public function actionLoadCities()
    {
       echo "    > load cities ... ";
        $path = Yii::getAlias('@console');
        $importer = new CSVImporter;
        $importer->setData(new CSVReader([
            'filename' => $path.'/data/locations/loc_city.csv',
            'fgetcsvOptions' => [
                'delimiter' => ","
            ]
        ]));
        $tableName = 'loc_city';
        $config = [
            [
                'attribute' => 'id',
                'value' => function($line) {
                    return $line[0];
                },
                'unique' => true,
            ],
            [
                'attribute' => 'id_region',
                'value' => function($line) {
                    return $line[1];
                },
                'unique' => false,
            ],
            [
                'attribute' => 'id_province',
                'value' => function($line) {
                    return $line[2];
                },
                'unique' => false,
            ],
            [
                'attribute' => 'location',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[3]);
                },
                'unique' => true,
            ],
            [
                'attribute' => 'idstat',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[4]);
                },
                'unique' => true,
            ],
            [
                'attribute' => 'geographic_zone',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[5]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'codnuts2',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[6]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'codnuts3',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[7]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'codmetropolis',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[8]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'codistat',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[9]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'codcadastre',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[10]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'province_abbreviation',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[11]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'zip',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[12]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'codregion',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[13]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'isprovince',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[14]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'altimetric_area',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[15]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'altitude',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[16]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'iscoastal',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[17]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'codmountain',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[18]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'surface',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[19]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'population2011',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[20]);
                },
                'unique' => false,
            ],
            [
                'attribute' => 'phone_prefix',
                'value' => function($line) {
                    return iconv ( 'CP1252', 'UTF8', $line[21]);
                },
                'unique' => false,
            ]
        ];
        $importer->import(new MultipleImportStrategy([
            'tableName' => $tableName,
            'configs' => $config,
        ]));
       echo "done\n";
    }

}
