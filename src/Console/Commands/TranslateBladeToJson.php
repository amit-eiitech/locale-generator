<?php

namespace Eii\LocaleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\Translate\V3\TranslateTextRequest;
use Illuminate\Support\Facades\Http;

class TranslateBladeToJson extends Command
{
    protected $signature = 'lang:extract 
                           {file : Blade file (e.g., welcome, mail.notification)} 
                           {--locales= : Comma-separated list of locales (e.g., ja,es,de)} 
                           {--translate= : Translation provider (google,deepl)}';

    protected $description = 'Extract __() strings from a Blade file and auto-translate to JSON language files';

    public function handle()
    {
        $fileInput = $this->argument('file');
        $filePath = config('locale-generator.views_path') . '/' . str_replace('.', '/', $fileInput) . '.blade.php';
        $locales = explode(',', $this->option('locales') ?? '');
        $provider = $this->option('translate') ?? 'google';

        if (!File::exists($filePath)) {
            $this->error("Blade file not found: {$fileInput}");
            return 1;
        }

        $contents = File::get($filePath);
        preg_match_all('/__\([\'"](.+?)[\'"]\)/', $contents, $matches);
        $strings = array_unique($matches[1]);

        $this->info("Found " . count($strings) . " strings in {$fileInput}");

        $langPath = resource_path('lang');
        if (!File::exists($langPath)) {
            File::makeDirectory($langPath, 0755, true);
            $this->info("Created lang directory at: {$langPath}");
        }

        foreach ($locales as $locale) {
            $jsonPath = "{$langPath}/{$locale}.json";
            $translations = File::exists($jsonPath) ? json_decode(File::get($jsonPath), true) : [];

            foreach ($strings as $string) {
                if (!isset($translations[$string])) {
                    $translated = $this->translate($string, $locale, $provider);
                    $translations[$string] = $translated;
                }
            }

            File::put($jsonPath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info("Updated lang/{$locale}.json");
        }

        return 0;
    }

    protected function translate(string $text, string $targetLocale, string $provider): string
    {
        if ($provider === 'google') {
            try {
                $keyFilePath = config('locale-generator.google_translate.key_file');
                if (!$keyFilePath || !file_exists($keyFilePath)) {
                    throw new \Exception('Google Translate service account key file not found');
                }

                $client = new TranslationServiceClient([
                    'credentials' => $keyFilePath,
                ]);

                $request = (new TranslateTextRequest())
                    ->setContents([$text])
                    ->setTargetLanguageCode($targetLocale)
                    ->setSourceLanguageCode('en')
                    ->setParent($client::locationName(config('locale-generator.google_translate.project_id'), 'global'));

                $response = $client->translateText($request);

                $translations = $response->getTranslations();
                return $translations[0]->getTranslatedText() ?? $text;
            } catch (\Exception $e) {
                $this->error("Translation failed for {$text}: " . $e->getMessage());
                return $text;
            }
        } elseif ($provider === 'deepl') {
            try {
                $apiKey = config('locale-generator.deepl_api_key');
                if (!$apiKey) {
                    throw new \Exception('DeepL API key not configured');
                }

                $response = Http::withHeaders([
                    'Authorization' => 'DeepL-Auth-Key ' . $apiKey,
                ])->post('https://api-free.deepl.com/v2/translate', [
                    'text' => [$text],
                    'target_lang' => strtoupper($targetLocale),
                    'source_lang' => 'EN',
                ])->throw()->json();

                return $response['translations'][0]['text'] ?? $text;
            } catch (\Exception $e) {
                $this->error("Translation failed for {$text}: " . $e->getMessage());
                return $text;
            }
        }

        return "[{$targetLocale}] $text"; // Fallback
    }
}
