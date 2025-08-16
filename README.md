# Locale Generator

**Locale Generator** is a Laravel Artisan command that simplifies multilingual development by automatically extracting `__()` strings from Blade templates and generating JSON language files.  
It optionally integrates with **Google Cloud Translate** or **DeepL** to provide instant translations, helping you speed up localization workflows.

---

## Installation

```bash
composer require eii/locale-generator
```

## Setup

1. **Publish Configuration**:
   ```bash
   php artisan vendor:publish --tag=config
   ```

2. **Configure Translation Providers**:
   - **Google Cloud Translate**:
     - Create a Google Cloud service account and download the JSON key file.
     - Place the key file in `storage/app/google-credentials.json` or update the path in `config/locale-generator.php`.
     - Add to `.env`:
       ```env
       GOOGLE_TRANSLATE_KEY_FILE=/path/to/google-credentials.json
       GOOGLE_PROJECT_ID=your-project-id
       ```
   - **DeepL**:
     - Obtain a DeepL API key (free tier available).
     - Add to `.env`:
       ```env
       DEEPL_API_KEY=your-deepl-api-key
       ```

## Usage

Run the lang:extract command:

```bash
php artisan lang:extract welcome --locales=ja,es,de --translate=google
php artisan lang:extract mail.notification --locales=fr,es --translate=deepl
```

- `file`: Blade file name (e.g., `welcome`, `mail.notification`).
- `--locales`: Comma-separated list of target locales (e.g., `ja,es,de`).
- `--translate`: Translation provider (`google` or `deepl`).

## Configuration

Edit `config/locale-generator.php` to customize:
- `views_path`: Directory for Blade files (default: `resources/views`).
- `google_translate.key_file`: Path to Google Cloud service account JSON key.
- `google_translate.project_id`: Google Cloud project ID.
- `deepl_api_key`: DeepL API key.

## Requirements

- PHP 8.1+
- Laravel 9.0, 10.0, 11.0, 12.0
- Google Cloud Translate API (for `google` provider)
- DeepL API (for `deepl` provider)

## License

This package is open-sourced software licensed under the MIT license.