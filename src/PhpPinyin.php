<?php

namespace Zidon\PhpPinyin;

/**
 * PhpPinyin (Modernized)
 * * 一个基于现代 PHP (>= 7.4) 标准构建的高性能中文转拼音类库。
 * 专为现代 Web 框架 (如 CodeIgniter 4, Laravel) 设计，内存常驻，极速转换。
 *
 * @version 1.0.1
 * @author zidon
 * @copyright (c) 2024-2026, zidon
 * @license MIT
 * @link https://github.com/zidony/phppinyin
 */
class PhpPinyin
{
    public const VERSION = '1.0.1';
    public const DICT_UPDATED_AT = '2026-03-29'; // 字典最后修正时间

    /**
     * @var array|null 静态内存字典缓存，确保单次请求只加载一次 I/O
     */
    private static ?array $dictionary = null;

    /**
     * 1. 核心转拼音方法
     * * @param string $text 待转换的中文文本
     * @param string $delimiter 拼音之间的分隔符，默认为空格
     * @return string 转换后的拼音 (如: "PHP 是最好的语言" -> "PHP shi zui hao de yu yan")
     */
    public static function pinyin(string $text, string $delimiter = ' '): string
    {
        $tokens = self::tokenize($text);
        $result = [];

        foreach ($tokens as $token) {
            if (isset(self::$dictionary[$token])) {
                $result[] = self::$dictionary[$token];
            } else {
                $result[] = $token; // 英文和数字原样保留
            }
        }

        return implode($delimiter, $result);
    }

    /**
     * 2. 获取 SEO 友好的 Slug
     * * 强制转为小写，并且如果是未收录的生僻字会安全过滤，确保返回绝对安全的 URL 别名。
     *
     * @param string $text 待转换的中文文本
     * @param string $delimiter 单词之间的分隔符，默认为横杠 (-)
     * @return string 转换后的别名 (如: "秋季露营指南" -> "qiu-ji-lu-ying-zhi-nan")
     */
    public static function slug(string $text, string $delimiter = '-'): string
    {
        $tokens = self::tokenize($text);
        $result = [];

        foreach ($tokens as $token) {
            if (isset(self::$dictionary[$token])) {
                $result[] = self::$dictionary[$token];
            } elseif (preg_match('/^[a-zA-Z0-9]+$/', $token)) {
                $result[] = strtolower($token); // 英数转小写保留
            }
        }

        return implode($delimiter, $result);
    }

    /**
     * 3. 获取拼音首字母缩写
     * * @param string $text 待转换的中文文本
     * @return string 缩写字符串 (如: "中华人民共和国" -> "zhrmghg")
     */
    public static function abbr(string $text): string
    {
        $tokens = self::tokenize($text);
        $result = '';

        foreach ($tokens as $token) {
            if (isset(self::$dictionary[$token])) {
                $result .= self::$dictionary[$token][0];
            } elseif (preg_match('/^[a-zA-Z0-9]+$/', $token)) {
                $result .= $token; // 英数原样追加
            }
        }

        return $result;
    }

    /**
     * 4. 获取首字母大写的拼音 (常用于姓名或标题)
     *
     * @param string $text 待转换的中文文本
     * @param string $delimiter 分隔符，默认为空格
     * @return string 首字母大写拼音 (如: "张三" -> "Zhang San")
     */
    public static function name(string $text, string $delimiter = ' '): string
    {
        $tokens = self::tokenize($text);
        $result = [];

        foreach ($tokens as $token) {
            if (isset(self::$dictionary[$token])) {
                $result[] = ucfirst(self::$dictionary[$token]);
            } else {
                $result[] = $token;
            }
        }

        return implode($delimiter, $result);
    }

    /**
     * 底层分词引擎：智能拆分汉字与英文数字
     * * @param string $text
     * @return array
     */
    private static function tokenize(string $text): array
    {
        self::loadDictionary();

        // 宽进严出：如果是老的 GBK 编码传来，强制安全转换为 UTF-8
        $encoding = mb_detect_encoding($text, ['UTF-8', 'GBK', 'GB2312', 'BIG5'], true);
        if ($encoding !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $encoding ?: 'auto');
        }

        $text = trim($text);
        if ($text === '') {
            return [];
        }

        // 🌟 核心提速魔法：
        // \p{Han} 匹配任意所有合法的中文汉字（含扩展生僻字）
        // [a-zA-Z0-9]+ 匹配连在一起的英文单词或数字
        // 这一句正则，直接把文本拆成了极其干净的数组，并自动清除了毫无意义的标点符号！
        preg_match_all('/[\p{Han}]|[a-zA-Z0-9]+/u', $text, $matches);

        return $matches[0] ?? [];
    }

    /**
     * 将 pinyin-utf8.dat 文件加载到静态内存池中
     *
     * @throws \RuntimeException
     * @return void
     */
    private static function loadDictionary(): void
    {
        // 如果已经加载过，直接跳过 (Singleton)
        if (self::$dictionary !== null) {
            return;
        }

        self::$dictionary = [];
        $file = __DIR__ . '/../data/pinyin-utf8.dat';

        if (!is_file($file)) {
            throw new \RuntimeException("PhpPinyin 核心字典文件丢失: {$file}");
        }

        // 极速按行读取文件
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // 🌟 忽略注释行和空行
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            // 按照啊`a的格式切分
            $parts = explode('`', $line);
            if (isset($parts[1])) {
                self::$dictionary[$parts[0]] = $parts[1];
            }
        }
    }
}
