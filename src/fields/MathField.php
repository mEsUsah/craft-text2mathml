<?php

namespace mesusah\crafttext2mathml\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Cp;
use craft\helpers\ElementHelper;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use yii\db\ExpressionInterface;
use yii\db\Schema;
use mesusah\crafttext2mathml\controllers\FormulaController;

/**
 * Math Field field type
 */
class MathField extends Field
{
    public static function displayName(): string
    {
        return Craft::t('text2mathml', 'Math Field');
    }

    public static function icon(): string
    {
        return '@mesusah/crafttext2mathml/icon.svg';
    }

    public static function phpType(): string
    {
        return 'mixed';
    }

    public static function dbType(): array|string|null
    {
        // Replace with the appropriate data type this field will store in the database,
        // or `null` if the field is managing its own data storage.
        return Schema::TYPE_STRING;
    }

    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            // ...
        ]);
    }

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }

    public function getSettingsHtml(): ?string
    {
        return null;
    }

    public function normalizeValue(mixed $value, ?ElementInterface $element): mixed
    {
        // if CP request, return the input value
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            return $value;
        }

        // MathML output
        return FormulaController::find($element->id)->formula ?? null;
    }

    protected function inputHtml(mixed $value, ?ElementInterface $element, bool $inline): string
    {
        $output = FormulaController::find($element->id);
        // Render the HTML for the field
        return Craft::$app->getView()->renderTemplate('text2mathml/fields/mathField', [
            'field' => $this,
            'input' => $value ?? '',
            'output' => $output->formula ?? '',
            'dump' => $element,
        ]);
    }

    public function afterElementSave(ElementInterface $element, bool $isNew): void
    {
        // // never update if the element is a draft
        if (ElementHelper::isDraft($element)) {
            return;
        }
        
        $value = $element->getFieldValue($this->handle);
        try{
            // Update the output value after saving to the database
            $formula = FormulaController::getMathML($value);
        } catch (\Exception $e) {
            Craft::error('Error getting MathML: ' . $e->getMessage(), __METHOD__);
            $formula = '<p>API error</p>';
        }
        FormulaController::save($element->id, $formula);
    }

    public function getElementValidationRules(): array
    {
        return [];
    }

    protected function searchKeywords(mixed $value, ElementInterface $element): string
    {
        return StringHelper::toString($value, ' ');
    }

    public function getElementConditionRuleType(): array|string|null
    {
        return null;
    }

    public static function queryCondition(
        array $instances,
        mixed $value,
        array &$params,
    ): ExpressionInterface|array|string|false|null {
        return parent::queryCondition($instances, $value, $params);
    }
}
