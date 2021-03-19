<?php

namespace App\Console\Commands;


use App\Util\Misc;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LocaleImport extends Command
{

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'locale:import {locale}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Import translations from a .po file';

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

    $path_po_file = resource_path() . "/po_files/translations-{$locale}.po";
    if (!File::exists($path_po_file)) {
      $this->error("Translation file does not exist at {$path_po_file}");
      return -1;
    }

    $output = [];
    // load po file
    $translations = File::get($path_po_file);

    $lines = explode("\n", $translations);
    $line = current($lines);
    $file_key = null;
    $translation_key = null;
    $translation = null;
    $extracting_msgstr = false;
    $have_ctxt = false;
    while ($line !== false) {
      $line = trim($line);
      $next_line = next($lines);

      // first try to extract the context - needed to put translation into correct file with correct key
      if ($have_ctxt === false) {
        if (Str::startsWith($line, 'msgctxt')) {
          $items = explode(' ', $line);
          if (isset($items[1])) {
            $key = self::decode($items[1]);
            $translation_key = str_replace('.php:', '.', $key);

            $translation = '';

            $this->info($translation_key);
            $have_ctxt = true;
          }
        } else {
          $line = $next_line;
          continue;
        }
      } else {
        // we have a context - try to find the start of translation
        if (!$extracting_msgstr) {
          // ignore anything that doesnt start with msgstr - we dont need the untranslated text
          if (!Str::startsWith($line, 'msgstr')) {
            $line = $next_line;
            continue;
            // start of translation found. set extracting msgstr flag to true and extract first line
          } else {
            $extracting_msgstr = true;
            $translation .= self::decode(explode(' ', $line, 2)[1]);
          }
        } else {
          // multiline translation - add to current field
          if (Str::startsWith($line, '"')) {
            $translation .= self::decode($line);
            // end of translation - reset flags and keys
          } else if ($line === '') {

            data_set($output, $translation_key, $translation);
            $extracting_msgstr = false;
            $have_ctxt = false;
            $translation_key = null;
            $translation = '';
          }
        }
      }
      $line = $next_line;
    }

    $output_path = resource_path('lang/' . $locale);
    if (!File::exists($output_path)) {
      File::makeDirectory($output_path);
    }

    // create/replace translation files
    foreach ($output as $file => $data) {
      if (empty($data)) {
        continue;
      }
      $file_name = $output_path . '/' . $file . '.php';
      $content = "<?php\nreturn ";
      $content .= self::varExport($data);
      $content .= ";\n";

      File::replace($file_name, $content);
    }
  }

  /**
   * export var to a properly formated php array string
   *
   * @param mixed $var
   * @param string $indent
   * @return void
   */
  private static function varExport($var, $indent = '')
  {
    switch (gettype($var)) {
      case 'string':
        return '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';
      case 'array':
        $indexed = array_keys($var) === range(0, count($var) - 1);
        $r = [];
        foreach ($var as $key => $value) {
          $r[] = "$indent    "
            . ($indexed ? '' : self::varExport($key) . ' => ')
            . self::varExport($value, "$indent    ");
        }

        return "[\n" . implode(",\n", $r) . "\n" . $indent . ']';
      case 'boolean':
        return $var ? 'true' : 'false';
      default:
        return var_export($var, true);
    }
  }

  /**
   * Convert a string from its PO representation.
   */
  private static function decode(string $value)
  {
    if (!$value) {
      return '';
    }

    if ($value[0] === '"') {
      $value = substr($value, 1, -1);
    }

    return strtr(
      $value,
      [
        '\\\\' => '\\',
        '\\a' => "\x07",
        '\\b' => "\x08",
        '\\t' => "\t",
        '\\n' => "\n",
        '\\v' => "\x0b",
        '\\f' => "\x0c",
        '\\r' => "\r",
        '\\"' => '"',
      ]
    );
  }
}
