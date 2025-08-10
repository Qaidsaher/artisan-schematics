# Artisan Schematics

[![Tests](https://img.shields.io/badge/tests-passing-brightgreen)](./vendor/bin/pest)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE.md)
[![Packagist](https://img.shields.io/packagist/v/saher/artisan-schematics)](https://packagist.org/packages/saher/artisan-schematics)
[![GitHub Repo](https://img.shields.io/badge/github-repo-blue?logo=github)](https://github.com/Qaidsaher/artisan-schematics)

**Artisan Schematics** is the most powerful, extensible, and professional Laravel package for exporting your Eloquent models and PHP enums to TypeScript, Dart, Kotlin, and Swift. It is designed for teams and individuals who want seamless, type-safe, cross-platform development.

---

## 🚀 Features

- **Multi-language output:** TypeScript, Dart, Kotlin, Swift (easily add more)
- **Deep relationship support:** Handles all Eloquent relationships (hasOne, hasMany, belongsTo, belongsToMany, morphTo, morphMany, hasOneThrough, etc.)
- **Enum and custom cast detection**
- **Recursive dependency resolution** (all referenced models/enums are included)
- **Configurable output paths and language toggles**
- **Zero manual require/include: full autoloading**
- **Battle-tested:** Comprehensive test suite for all features
- **Extensible:** Add your own generators in minutes
- **Professional code output:** Idiomatic, readable, and ready for production

---

## 📦 Installation

```bash
composer require saher/artisan-schematics --dev
```

---

## ⚙️ Configuration

Publish the config file:

```bash
php artisan vendor:publish --provider="Saher\ArtisanSchematics\ArtisanSchematicsServiceProvider"
```

Edit `config/schematics.php` to enable/disable languages and set output paths for each target.

---

## 🛠️ Usage

Export all models and enums:

```bash
php artisan schematics:export
```

Or specify custom paths:

```bash
php artisan schematics:export --paths=app/Models,app/Enums
```

---

## 📂 Output

- **TypeScript:** `resource/ts/schemas` (default)
- **Dart:** `tests/output/dart` (customizable)
- **Kotlin:** `tests/output/kotlin` (customizable)
- **Swift:** `tests/output/swift` (customizable)

---

## 🧠 What gets generated?

- All models, enums, and their relationships (including advanced: morphs, through, etc.)
- All referenced types recursively (no missing dependencies)
- Output files for each language (e.g., `Post.ts`, `PostStatus.dart`, `User.kt`, `Tag.swift`, etc.)

---

## 💡 Example

### Models

```php
class User extends Model {
	public function posts() { return $this->hasMany(Post::class); }
	public function tags() { return $this->belongsToMany(Tag::class); }
	public function comments() { return $this->morphMany(Comment::class, 'commentable'); }
	public function country() { return $this->hasOneThrough(Country::class, Address::class); }
}

class Post extends Model {
	protected $casts = [
		'status' => PostStatus::class,
		'tags' => 'array',
	];
	public function author() { return $this->belongsTo(User::class); }
}

class Comment extends Model {
	public function post() { return $this->hasMany(Post::class); }
	public function tags() { return $this->belongsToMany(Tag::class); }
}

class Tag extends Model {}
class Country extends Model {}
class Address extends Model {}
```

### Enum

```php
enum PostStatus: string { case DRAFT = 'draft'; case PUBLISHED = 'published'; }
```

### Output

- `User.ts`, `Post.ts`, `Comment.ts`, `Tag.ts`, `Country.ts`, `Address.ts`, `PostStatus.ts`
- `user.dart`, `post.dart`, `comment.dart`, `tag.dart`, `country.dart`, `address.dart`, `post_status.dart`
- `User.kt`, `Post.kt`, `Comment.kt`, `Tag.kt`, `Country.kt`, `Address.kt`, `PostStatus.kt`
- `User.swift`, `Post.swift`, `Comment.swift`, `Tag.swift`, `Country.swift`, `Address.swift`, `PostStatus.swift`

---

## 🧪 Testing

Run the test suite:

```bash
./vendor/bin/pest
```

Tests assert that all expected files are generated for all languages, including enums and all relationship types.

---

## 🧩 Extending

Add your own generator by implementing `GeneratorContract` and registering it in `config/schematics.php`:

```php
'go' => [
	'enabled' => true,
	'generator' => \App\Generators\GoGenerator::class,
	'output_path' => base_path('go/models'),
],
```

---

## 🛠️ Advanced Usage & Tips

- **Custom relationships:** All Eloquent relationship types are supported out of the box.
- **Enum support:** Backed and pure enums are both supported.
- **Custom casts:** Custom cast types are detected and exported.
- **Zero manual require/include:** All files are autoloaded and analyzed recursively.
- **CI/CD ready:** Add `./vendor/bin/pest` to your pipeline to ensure your contracts are always up to date.

---

## ❓ Troubleshooting

- **Missing files?** Ensure your models/enums are in the scanned paths and autoloaded by Composer.
- **Custom output?** Edit `config/schematics.php` to change output directories or add new languages.
- **Need more?** Open an issue or PR!

---

## 🤝 Community & Contributing

Pull requests, issues, and feature requests are welcome! Help make Artisan Schematics the standard for cross-platform Laravel development.

---

## 📄 License

MIT

---

## 📦 Releases & Tags

- [View latest release and all tags on GitHub](https://github.com/Qaidsaher/artisan-schematics/releases)
- [View on Packagist](https://packagist.org/packages/saher/artisan-schematics)