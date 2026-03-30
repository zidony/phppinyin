<?php

namespace Zidon\PhpPinyin;

/**
 * PhpPinyin
 * 一个基于现代 PHP (>= 7.4) 标准构建的高性能中文转拼音组件。
 * 专为现代 Web 框架设计，OPcache 内存常驻，原生支持声调解析与无损降级。
 *
 * @version 1.1.0
 * @author zidon
 * @copyright (c) 2024-2026, zidon
 * @license MIT
 * @link https://github.com/zidony/phppinyin
 */
class PhpPinyin
{
    public const VERSION = '1.1.0';
    public const DICT_UPDATED_AT = '2026-03-28';

    /**
     * @var array|null 静态内存字典缓存 (依赖 OPcache 实现极速加载)
     */
    private static ?array $dictionary = null;

    /**
     * 声调降级映射表：将带有声调的 UTF-8 拼音字符降级为标准 a-z ASCII
     * 涵盖基础元音以及 ḿ, ń, ế 等发音边缘 Case
     */
    private const TONE_MAP = [
        'ā' => 'a',
        'á' => 'a',
        'ǎ' => 'a',
        'à' => 'a',
        'ō' => 'o',
        'ó' => 'o',
        'ǒ' => 'o',
        'ò' => 'o',
        'ē' => 'e',
        'é' => 'e',
        'ě' => 'e',
        'è' => 'e',
        'ī' => 'i',
        'í' => 'i',
        'ǐ' => 'i',
        'ì' => 'i',
        'ū' => 'u',
        'ú' => 'u',
        'ǔ' => 'u',
        'ù' => 'u',
        'ǖ' => 'v',
        'ǘ' => 'v',
        'ǚ' => 'v',
        'ǜ' => 'v',
        'ü' => 'v',
        'ń' => 'n',
        'ň' => 'n',
        'ǹ' => 'n',
        'ḿ' => 'm',
        'm̀' => 'm',
        'ế' => 'e',
        'ề' => 'e',
        'ễ' => 'e',
        'ệ' => 'e',
    ];

    /**
     * 1. 基础转拼音 (无声调 - 保持向后兼容)
     *
     * @param string $text 待转换文本
     * @param string $delimiter 分隔符，默认为空格
     * @return string 转换后的拼音 (如: "PHP 是最好的语言" -> "PHP shi zui hao de yu yan")
     */
    public static function pinyin(string $text, string $delimiter = ' '): string
    {
        $tokens = self::tokenize($text);
        $result = [];

        foreach ($tokens as $token) {
            if (isset(self::$dictionary[$token])) {
                $result[] = strtr(self::$dictionary[$token], self::TONE_MAP);
            } else {
                $result[] = $token;
            }
        }

        return implode($delimiter, $result);
    }

    /**
     * 1.1 [NEW] 基础转拼音 (带声调)
     *
     * @param string $text 待转换文本
     * @param string $delimiter 分隔符，默认为空格
     * @return string 转换后的带调拼音 (如: "中国" -> "zhōng guó")
     */
    public static function pinyinTone(string $text, string $delimiter = ' '): string
    {
        $tokens = self::tokenize($text);
        $result = [];

        foreach ($tokens as $token) {
            if (isset(self::$dictionary[$token])) {
                $result[] = self::$dictionary[$token];
            } else {
                $result[] = $token;
            }
        }

        return implode($delimiter, $result);
    }

    /**
     * 2. 获取 SEO 友好的 Slug (强制无声调、小写、去杂质)
     *
     * @param string $text 待转换文本
     * @param string $delimiter 分隔符，默认为横杠 (-)
     * @return string 安全的 URL 别名 (如: "2026 露营指南！" -> "2026-lu-ying-zhi-nan")
     */
    public static function slug(string $text, string $delimiter = '-'): string
    {
        $tokens = self::tokenize($text);
        $result = [];

        foreach ($tokens as $token) {
            if (isset(self::$dictionary[$token])) {
                $result[] = strtr(self::$dictionary[$token], self::TONE_MAP);
            } elseif (preg_match('/^[a-zA-Z0-9]+$/', $token)) {
                $result[] = strtolower($token);
            }
        }

        return implode($delimiter, $result);
    }

    /**
     * 3. 获取拼音首字母缩写 (强制无声调)
     *
     * @param string $text 待转换文本
     * @return string 缩写字符串 (如: "中华人民共和国" -> "zhrmghg")
     */
    public static function abbr(string $text): string
    {
        $tokens = self::tokenize($text);
        $result = '';

        foreach ($tokens as $token) {
            if (isset(self::$dictionary[$token])) {
                $cleanPinyin = strtr(self::$dictionary[$token], self::TONE_MAP);
                $result .= $cleanPinyin[0];
            } elseif (preg_match('/^[a-zA-Z0-9]+$/', $token)) {
                $result .= $token;
            }
        }

        return $result;
    }

    /**
     * 4. 获取首字母大写的拼音 (无声调 - 保持向后兼容)
     *
     * @param string $text 待转换文本
     * @param string $delimiter 分隔符，默认为空格
     * @return string 首字母大写的拼音 (如: "张三" -> "Zhang San")
     */
    public static function name(string $text, string $delimiter = ' '): string
    {
        $tokens = self::tokenize($text);
        $result = [];

        foreach ($tokens as $token) {
            if (isset(self::$dictionary[$token])) {
                $cleanPinyin = strtr(self::$dictionary[$token], self::TONE_MAP);
                $result[] = ucfirst($cleanPinyin);
            } else {
                $result[] = $token;
            }
        }

        return implode($delimiter, $result);
    }

    /**
     * 4.1 [NEW] 获取首字母大写的拼音 (带声调)
     *
     * @param string $text 待转换文本
     * @param string $delimiter 分隔符，默认为空格
     * @return string 首字母大写且带调的拼音 (如: "张三" -> "Zhāng Sān")
     */
    public static function nameTone(string $text, string $delimiter = ' '): string
    {
        $tokens = self::tokenize($text);
        $result = [];

        foreach ($tokens as $token) {
            if (isset(self::$dictionary[$token])) {
                $result[] = mb_convert_case(self::$dictionary[$token], MB_CASE_TITLE, 'UTF-8');
            } else {
                $result[] = $token;
            }
        }

        return implode($delimiter, $result);
    }

    /**
     * 底层分词引擎
     */
    private static function tokenize(string $text): array
    {
        self::loadDictionary();

        $encoding = mb_detect_encoding($text, ['UTF-8', 'GBK', 'GB2312', 'BIG5'], true);
        if ($encoding !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $encoding ?: 'auto');
        }

        $text = trim($text);
        if ($text === '') {
            return [];
        }

        preg_match_all('/[\p{Han}]|[a-zA-Z0-9]+/u', $text, $matches);

        return $matches[0] ?? [];
    }

    /**
     * 将 pinyin_dict.php 数组加载到内存中
     * 即使在未开启 OPcache 的环境中，require 原生 PHP 数组依然是最优解
     */
    private static function loadDictionary(): void
    {
        if (self::$dictionary !== null) {
            return;
        }

        $file = __DIR__ . '/../data/pinyin_dict.php';

        if (!is_file($file)) {
            throw new \RuntimeException("PhpPinyin 核心字典文件丢失: {$file}");
        }

        self::$dictionary = require $file;
    }
}
