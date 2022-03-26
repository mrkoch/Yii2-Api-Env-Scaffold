<?php

use yii\db\Migration;

/**
 * Class m201116_213701_add_locations
 */
class m201116_213701_add_locations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('loc_city', [
            'id' => $this->primaryKey(),
            'id_region' => $this->integer(),
            'id_province' => $this->integer(),
            'location' => $this->string(255),
            'idstat' => $this->integer(10),
            'geographic_zone' => $this->string(255),
            'codnuts2' => $this->string(255),
            'codnuts3' => $this->string(255),
            'codmetropolis' => $this->string(255),
            'codistat' => $this->string(255),
            'codcadastre' => $this->string(255),
            'province_abbreviation' => $this->string(255),
            'zip' => $this->string(255),
            'codregion' => $this->string(255),
            'isprovince' => $this->string(255),
            'altimetric_area' => $this->string(255),
            'altitude' => $this->string(255),
            'iscoastal' => $this->string(255),
            'codmountain' => $this->string(255),
            'surface' => $this->string(255),
            'population2011' => $this->string(255),
            'phone_prefix' => $this->string(255)
        ]);

        $this->createTable('loc_continent', [
            'id' => $this->primaryKey(),
            'name' => $this->string(7),
            'name_en' => $this->string(9),
        ]);

        $this->createTable('loc_nation', [
            'id' => $this->primaryKey(),
            'idcontinent' => $this->integer(1),
            'idarea' => $this->integer(2),
            'abbreviation' => $this->string(3),
            'name' => $this->string(70),
            'name_en' => $this->string(50),
        ]);

        $this->createTable('loc_province', [
            'id' => $this->primaryKey(),
            'id_region' => $this->integer(),
            'province' => $this->string(255),
            'abbreviation' => $this->string(2),
            'coddivision' => $this->string(255),
            'codnuts1' => $this->string(255),
            'geographical_area' => $this->string(255),
            'codnuts2' => $this->string(255),
            'region' => $this->string(255),
            'codmetropolis' => $this->string(255),
            'codnuts3' => $this->string(255)
        ]);

        $this->createTable('loc_region', [
            'id' => $this->primaryKey(),
            'region' => $this->string(255)
        ]);

        $this->addForeignKey(
            'fk_city_region',
            'loc_city',
            'id_region',
            'loc_region',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk_city_province',
            'loc_city',
            'id_province',
            'loc_province',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk_province_region',
            'loc_province',
            'id_region',
            'loc_region',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk_city_region',
            'loc_city'
        );

        $this->dropForeignKey(
            'fk_city_province',
            'loc_city'
        );

        $this->dropForeignKey(
            'fk_province_region',
            'loc_province'
        );

        $this->dropTable('loc_city');

        $this->dropTable('loc_continent');

        $this->dropTable('loc_nation');

        $this->dropTable('loc_province');

        $this->dropTable('loc_region');
    }
}
