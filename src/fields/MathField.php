<?php

namespace mesusah\crafttext2mathml\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Cp;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use yii\db\ExpressionInterface;
use yii\db\Schema;

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
        return $value;
    }

    protected function inputHtml(mixed $value, ?ElementInterface $element, bool $inline): string
    {
        $fieldData = json_decode($value);

        // Render the HTML for the field
        return Craft::$app->getView()->renderTemplate('text2mathml/fields/mathField', [
            'field' => $this,
            'input' => $fieldData->input ?? '',
            'output' => $fieldData->output ?? '',
            'valueDecoded' => $fieldData,
        ]);
    }

    public function beforeElementSave(ElementInterface $element, bool $isNew): bool
    {
        // Update the output value before saving to the database
        if ($element->isFieldDirty($this->handle)) {
            $value = $element->getFieldValue($this->handle);

            $mathml = $this->getMathML($value);
            $mathml = str_replace('<pre>', '', $mathml);
            $mathml = str_replace('</pre>', '', $mathml);
            $mathml = str_replace('&lt;', '<', $mathml);
            $mathml = str_replace('&gt;', '>', $mathml);
            $mathml = str_replace('&gt;', '>', $mathml);
            $mathml = str_replace('&#39;', "'",$mathml);
            $mathml = str_replace('\n', '',$mathml);

            $valueJson = json_encode([
                'input' => $value,
                'output' => $mathml,
            ]);
            $element->setFieldValue($this->handle, $valueJson);
        }
        return parent::beforeElementSave($element, $isNew);
    }

    private function getMathML(string $input): string
    {
        // contact the MathML API to convert the input to MathML
        try {
            $data = [
                'tst1' => '{"delivery" ->"embed","character" ->"mathml","indent" ->"false","markup" ->"presentation","declare" ->"false","document" ->"false"}',
                'formattype1' => 'MathML',
                'txt' => $input,
                'type' => 'txt',
                'formname' => 'tomathml',
                'form' => 'TraditionalForm',
            ];
            
            $ch = curl_init('https://www.mathmlcentral.com/Tools/XhtmlResult.jsp');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: text/html, */*',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
                'x-requested-with: XMLHttpRequest',
                'host: www.mathmlcentral.com',
                'referer: https://www.mathmlcentral.com/Tools/ToMathML.jsp',
            ]);
            
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response === false) {
                throw new \Exception('Failed to fetch MathML from the API');
            }
            return $response;
        } catch (\Exception $e) {
            Craft::error('Error fetching MathML: ' . $e->getMessage(), __METHOD__);
            return '<p>API error</p>';
        }
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
