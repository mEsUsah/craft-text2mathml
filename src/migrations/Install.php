<?php

namespace mesusah\crafttext2mathml\migrations;

use Craft;
use craft\db\Migration;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Create table tryhackme_country
        $this->createTable('{{%text2mathml_formula}}', [
            'id integer auto_increment primary key',
            'elementId integer',
            'formula text',
        ]);

        // Add foreign key for element
        $this->addForeignKey(
            'fk-text2mathml_element_formula',
            '{{%text2mathml_formula}}',
            'elementId',
            '{{%elements}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%text2mathml_formula}}');

        return true;
    }
}
