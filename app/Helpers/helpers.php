<?php

if (!function_exists('generateCode')) {
    function generateCode($model, string $column, int $length = 4): string
    {
        $lastRecord = $model::orderBy($column, 'desc')->first();

        $lastCode = $lastRecord ? $lastRecord->$column : null;

        $code = $lastCode ? (int) $lastCode + 1 : 1;

        return str_pad($code, $length, '0', STR_PAD_LEFT);
    }
}
