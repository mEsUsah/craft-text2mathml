<?php

namespace mesusah\crafttext2mathml\controllers;

use Craft;
use craft\web\Controller;
use mesusah\crafttext2mathml\models\Formula;
use yii\web\Response;

/**
 * Formula controller
 */
class FormulaController extends Controller
{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_LIVE;

    /**
     * text2mathml/formula action
     */

    public static function find(int $elementId): Formula|null
    {
        $formula = Formula::find()
            ->where(['elementId' => $elementId])
            ->one();

        if (!$formula) {
            return null;
        }

        return $formula;
    }

    public static function save(int $elementId, string $formula): Formula|null
    {
        $formulaModel = self::find($elementId);

        if (!$formulaModel) {
            $formulaModel = new Formula();
            $formulaModel->elementId = $elementId;
        }

        $formulaModel->formula = $formula;

        if ($formulaModel->save()) {
            return $formulaModel;
        }

        return null;
    }

    public static function getMathML(string $input): string
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
            ]);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            // Check http response code
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
                throw new \Exception('Curl error: ' . curl_error($ch));
            }
            
            if ($response === false) {
                throw new \Exception('API call failed');
            }

            return self::cleanUpMathML($response);

        } catch (\Exception $e) {
            Craft::error('Error fetching MathML: ' . $e->getMessage(), __METHOD__);
            return '<p><em>API error - Unable to contact service!</em></p>';
        }
    }

    public static function cleanUpMathML(string $mathml): string
    {
        $mathml = str_replace('<pre>', '', $mathml);
        $mathml = str_replace('</pre>', '', $mathml);
        $mathml = str_replace('&lt;', '<', $mathml);
        $mathml = str_replace('&gt;', '>', $mathml);
        $mathml = str_replace('&gt;', '>', $mathml);
        $mathml = str_replace('&#39;', "'",$mathml);
        return $mathml;
    }

}
