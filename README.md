# PhpPinyin

🚀 一个专为现代 PHP (>= 7.4) 设计的高性能、零依赖中文转拼音组件。

PhpPinyin 摒弃了早期类库中臃肿的双轨制（GBK/UTF-8）编码处理和低效的循环截取，采用纯 UTF-8 内存字典与 PCRE 正则引擎，在保证极高转换精度的同时，提供了极致的运行性能。完美适配 CodeIgniter 4、Laravel 等现代 Web 框架的 SEO 别名生成与拼音检索需求。

## ✨ 特性 (Features)

- **极简接口**：提供少量核心 API（全拼、Slug、缩写、名称格式化、声调输出），覆盖绝大多数业务场景。
- **性能强悍**：基于 `\p{Han}` 的高效分词 + 内存常驻字典 + 分词结果缓存机制，在高频调用场景下具备极低的 CPU 与 I/O 开销。
- **现代化架构**：全面支持 PHP 7.4+ 强类型提示，遵循 PSR-4 自动加载规范。
- **开箱即用**：零外部依赖（不需要底层 C 扩展，不需要 Composer 也能完美运行）。

## 📦 安装 (Installation)

本项目提供轻量级的手动部署方式，非常适合追求极致掌控力的项目。

> ⚠️ 本项目暂未发布至 Packagist（Composer），如有需要可自行封装或通过 VCS 引入。

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

// 如果已有分词结果（例如来自业务逻辑或第三方分词器），可以实现词组级拼音拼接（推荐）
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

### 5. 带声调输出 `pinyinTone()` / `nameTone()` [v1.1.0 新增]
精准输出汉字的拼音注音，包含声调标号，完美适用于前台注音展示。

```php
$text = "中国";

// 带声调输出
echo PhpPinyin::pinyinTone($text);
// 输出: zhōng guó

// 带声调且首字母大写
echo PhpPinyin::nameTone($text);
// 输出: Zhōng Guó
```

## ⚠️ 注意事项 (Notes)

- 本组件基于“单字映射”实现，不包含中文分词与语义分析能力。
- 对于多音字（如“乐”、“行”、“重”），默认使用常见读音：
  - 音乐 → yin le（非 yue）
- 如需更高准确率，建议结合业务词库或分词结果进行二次处理。

## 🛠️ 数据字典更新

内置的 `data/pinyin_dict.php` 包含了常用汉字的拼音映射。如果你发现生僻字遗漏或多音字错误，可以直接修改该 PHP 数组文件（需保持返回结构为 `array<string, string>`）。

## 📌 Roadmap

- [ ] 多音字词库支持（Polyphonic Words）
- [ ] 词级分词优化（FMM）
- [ ] 用户自定义词典

## 🙏 鸣谢 (Acknowledgments)

本项目的核心字典数据（2 万字扩容版 `pinyin_dict.php`）提取自开源项目 [`itplato/phpanalysis`](https://github.com/itplato/phpanalysis) 未竟的 5.0 开发分支。

在此，我们怀着崇敬与惋惜的心情，特别致敬并鸣谢该项目的原作者 **itplato**。他是一位极其优秀的中文 PHP 开发者，令人扼腕的是，itplato 已于数年前与世长辞，他在该分支中探索的诸多开创性重构与数据扩容也因此未能正式发布。

本项目有幸将他生前整理的这份珍贵字库延续下来，通过 OPcache 优化接入现代 Web 框架，使其在新的时代继续发挥价值，也是对这位开源先行者最好的缅怀。

同时，这份数据也离不开早期开源社区对中文 NLP 数据基础建设的长期沉淀，向所有默默奉献的开发者们一并致谢。

## 📄 版权与许可 (License)

本项目基于 [MIT License](LICENSE) 开源。

Copyright (c) 2024-2026, zidon. 欢迎提交 Issue 和 Pull Request！