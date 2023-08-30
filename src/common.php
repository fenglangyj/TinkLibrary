<?php

declare (strict_types=1);

if (!function_exists('p')) {
    /**
     * 打印输出数据到文件
     * @param mixed $data 输出的数据
     * @param boolean $new 强制替换文件
     * @param ?string $file 保存文件名称
     * @return false|int
     */
    function p($data, bool $new = false, ?string $file = null)
    {
        ob_start();
        var_dump($data);
        $output = preg_replace('/]=>\n(\s+)/m', '] => ', ob_get_clean());
        if (is_null($file)) $file = syspath('runtime/' . date('Ymd') . '.log');
        else if (!preg_match('#[/\\\\]+#', $file)) $file = syspath("runtime/{$file}.log");
        is_dir($dir = dirname($file)) or mkdir($dir, 0777, true);
        return $new ? file_put_contents($file, $output) : file_put_contents($file, $output, FILE_APPEND);
    }

}