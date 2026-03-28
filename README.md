# PhpPinyin

🚀 一个专为现代 PHP (>= 7.4) 设计的高性能中文转拼音轻量级组件。

PhpPinyin 摒弃了早期类库中臃肿的双轨制（GBK/UTF-8）编码处理和低效的循环截取，采用纯 UTF-8 内存字典与 PCRE 正则引擎，在保证极高转换精度的同时，提供了极致的运行性能。完美适配 CodeIgniter 4、Laravel 等现代 Web 框架的 SEO 别名生成与拼音检索需求。

## ✨ 特性 (Features)

- **极简接口**：摒弃繁琐的历史方法，仅暴露 4 个核心 API，满足绝大多数业务场景。
- **性能强悍**：利用 `\p{Han}` 智能分词，底层使用内存常驻的单例字典，杜绝重复的 I/O 开销。
- **现代化架构**：全面支持 PHP 7.4+ 强类型提示，遵循 PSR-4 自动加载规范。
- **开箱即用**：零外部依赖（不需要底层 C 扩展，不需要 Composer 也能完美运行）。

## 📦 安装 (Installation)

本项目提供轻量级的手动部署方式，非常适合追求极致掌控力的项目。

1. 下载本项目源码包。
2. 将整个 `phppinyin` 文件夹放入你的项目第三方扩展目录（如 CI4 的 `app/ThirdParty/`）。
3. 在你的框架中注册命名空间 `Zidon\PhpPinyin` 指向本项目的 `src/` 目录。

**CodeIgniter 4 配置示例 (`app/Config/Autoload.php`)：**
```php
public $psr4 = [
    // ...
    'Zidon\PhpPinyin' => APPPATH . 'ThirdParty/phppinyin/src',
];
```

## 💡 使用方法 (Usage)

### 1. 基础转拼音 `pinyin()`
将汉字转为全拼，保留英文字母和数字，默认使用空格分隔。

```php
use Zidon\PhpPinyin\PhpPinyin;

$text = "PHP 是最好的语言 8.0";
echo PhpPinyin::pinyin($text); 
// 输出: PHP shi zui hao de yu yan 8.0

// 自定义分隔符
echo PhpPinyin::pinyin($text, '-'); 
// 输出: PHP-shi-zui-hao-de-yu-yan-8.0
```

### 2. 生成 SEO 友好的 URL 别名 `slug()`
强制转为小写，并自动过滤掉所有标点符号和不可见字符，专为生成 URL Slug 设计。

```php
$title = "2026 户外露营指南！";
echo PhpPinyin::slug($title);
// 输出: 2026-hu-wai-lu-ying-zhi-nan

// 配合中文分词库实现词组拼音连写 (推荐用法)
$words = ['2026', '户外', '露营', '指南'];
$slugParts = [];
foreach ($words as $word) {
    $slugParts[] = PhpPinyin::slug($word, ''); // 词组内部不加分隔符
}
echo implode('-', $slugParts);
// 输出: 2026-huwai-luying-zhinan
```

### 3. 获取首字母缩写 `abbr()`
提取每个汉字的拼音首字母，常用于快速检索。

```php
$name = "中华人民共和国";
echo PhpPinyin::abbr($name);
// 输出: zhrmghg
```

### 4. 拼音首字母大写 `name()`
每个拼音单词的首字母大写，适用于人名或标准标题。

```php
$name = "张三";
echo PhpPinyin::name($name);
// 输出: Zhang San
```

## 🛠️ 数据字典更新

内置的 `data/pinyin-utf8.dat` 包含了常用汉字的拼音映射。如果你发现生僻字遗漏或多音字错误，可以直接修改该文本文件。文件支持使用 `#` 开头添加注释。

## 🙏 鸣谢 (Acknowledgments)

本项目的核心字典数据 (`pinyin-utf8.dat`) 衍生自开源社区长期沉淀的中文拼音映射库（早期广泛应用于各类输入法与多语言拼音转换组件中）。感谢无数开源前辈对中文 NLP 数据基础建设的默默贡献。本项目在此数据基础上进行了人工勘误、生僻字补全以及针对现代框架的重构优化。

## 📄 版权与许可 (License)

本项目基于 [MIT License](LICENSE) 开源。

Copyright (c) 2024-2026, zidon. 欢迎提交 Issue 和 Pull Request！