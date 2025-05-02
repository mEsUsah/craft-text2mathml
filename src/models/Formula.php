<?php

namespace mesusah\crafttext2mathml\models;

use Craft;
use craft\db\ActiveRecord;

/**
 * Element Formula model
 */
class Formula extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%text2mathml_formula}}';
    }
}
