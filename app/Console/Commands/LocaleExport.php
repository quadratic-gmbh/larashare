<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class LocaleExport extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'locale:export {locale}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Export translation arrays as .po file';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $locale = $this->argument('locale');

    $path_locale = resource_path('lang/' . $locale);

    $output = '';

    // load translation arrays from default locale
    $files = File::files(resource_path('lang/' . config('app.locale')));
    foreach($files as $file) {
      if ($file->getExtension() !== 'php') {
        continue;
      }
      // import translation arrays
      $file_path_locale = $path_locale . '/' . $file->getFilename();
      $data_default = Arr::dot(include $file->getPathname());
      $data_export = [];
      if (File::exists($file_path_locale)) {
        $data_export = include $file_path_locale;
      }
      /*
       * po file structure:
       * each translation should have
       * - whitespace
       * - msgctxt: provide context for duplicate msgids (will be array key in
       *    dot notation)
       * - msgid: untranslated string
       * - msgtr: translation
       */
      $domain = $file->getBasename();
      foreach($data_default as $key => $value) {
        $domain_key = "{$domain}:{$key}";
        $this->info($domain_key);
        $output .= "msgctxt \"{$domain_key}\"\n";

        $output .= 'msgid ';
        $value = str_replace("\"",'\"', $value);
        $msgid_lines = explode("\n", $value);
        $msgid_line_count = count($msgid_lines);
        if ($msgid_line_count > 1) {
          $output .= "\"\"\n";
          for($i = 0; $i < $msgid_line_count; $i++) {
            $msgid_line = $msgid_lines[$i];
            $output .= "\"" . $msgid_line;
            if ($i !== $msgid_line_count-1) {
              $output .= "\\n";
            }
            $output .="\"\n";
          }
        } else {
          $output .= "\"{$value }\"\n";
        }

        $output .= 'msgstr ';
        $msgstr = str_replace("\"", '\"', data_get($data_export, $key));
        $msgstr_lines = explode("\n", $msgstr);
        $msgstr_line_count = count($msgstr_lines);
        if ($msgstr_line_count > 1) {
          $output .= "\"\"\n";
          for($i = 0; $i < $msgstr_line_count; $i++) {
            $msgstr_line = $msgstr_lines[$i];
            $output .= "\"" . $msgstr_line;
            if ($i !== $msgstr_line_count-1) {
              $output .= "\\n";
            }
            $output .="\"\n";
          }
        } else {
          $output .="\"{$msgstr}\"\n";
        }

        $output .= "\n";
      }
    }

    $output_path = resource_path() . '\\po_files';
    File::replace("{$output_path}\\translations-{$locale}.po", $output);
  }
}
