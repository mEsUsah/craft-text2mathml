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
            return self::cleanUpMathML($response);
        } catch (\Exception $e) {
            Craft::error('Error fetching MathML: ' . $e->getMessage(), __METHOD__);
            return '<p>API error</p>';
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
