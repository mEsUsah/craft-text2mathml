<?php

namespace mesusah\crafttext2mathml\controllers;

use craft\web\Controller;
use mesusah\crafttext2mathml\models\Formula;

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
        // Convert inner a/b first, then handle a/(expr) so nested fractions render correctly
        $latex = preg_replace('/([a-zA-Z0-9_]+)\/([a-zA-Z0-9_]+)/', '\\frac{$1}{$2}', $input);
        $latex = preg_replace('/([a-zA-Z0-9_]+)\/\(([^)]+)\)/', '\\frac{$1}{$2}', $latex);
        return '$$' . trim($latex) . '$$';
    }

}
