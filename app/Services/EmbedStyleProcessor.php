<?php
/**
 * EmbedStyleProcessor.php
 */
namespace App\Services;

use App\Embed;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;



/**
 * EmbedStyleProcessor
 *
 * @author vadler
 *
 */
class EmbedStyleProcessor
{
  protected $dest_path = 'app/embeds/';
  protected $src_path = 'sass/embeds';
  
  /**
   * get path of original embed.scss
   * @return string
   */
  public function getSrcPath()
  {
    return resource_path($this->src_path);
  }
  
  /**
   * Get destination path for embed.
   * 
   * @param Embed $embed
   * @return string
   */
  public function getDestPath(Embed $embed)
  {
    $path = storage_path($this->dest_path . $embed->id);
    
    if(!File::exists($path)) {
      File::makeDirectory($path, 0755, true);
    }
    
    return $path;
  }
  
  /**
   * Get path for advanced scss file.
   * 
   * @param Embed $embed
   * @return string
   */
  public function getAdvancedPath(Embed $embed)
  {
    return $this->getDestPath($embed) . '/advanced.scss';
  }
  
  /**
   * Get path for simple.scss.
   * 
   * @param Embed $embed
   * @return string
   */
  public function getSimplePath(Embed $embed)
  {
    return $this->getDestPath($embed) . '/simple.scss';
  }
  
  /**
   * Get path for advanced variables file.
   * 
   * @param Embed $embed
   * @return string
   */
  public function getAdvancedVariablesPath(Embed $embed)
  {
    return $this->getDestPath($embed) . '/advanced_variables.scss';
  }
  
  /**
   * Get hash of advanced file.
   * 
   * @param Embed $embed
   * @return NULL|string
   */
  public function getAdvancedHash(Embed $embed)
  {
    $dest = $this->getAdvancedPath($embed);
    return (File::exists($dest) ? File::hash($dest) : null);
  }
  
  /** 
   * Get hash of advanced vars file.
   * 
   * @param Embed $embed
   * @return NULL|string
   */
  public function getAdvancedVariablesHash(Embed $embed)
  {
    $dest = $this->getAdvancedVariablesPath($embed);
    return (File::exists($dest) ? File::hash($dest) : null);
  }
  
  /**
   * Stores simple embed style.
   * 
   * @param Embed $embed
   * @return boolean
   */
  public function storeSimple(Embed $embed)
  {
    $dest = $this->getSimplePath($embed);
    
    return (File::put($dest, $this->renderSimple($embed)) !== false);
  }
  
  /**
   * Store advanced embed style.
   * 
   * @param Embed $embed
   * @param string $content
   * @return boolean
   */
  public function storeAdvanced(Embed $embed, $content)
  {
    $dest = $this->getAdvancedPath($embed);
    
    return (File::put($dest, $content) !== false);
  }
  
  /**
   * 
   * Store advanced variables.
   * 
   * @param Embed $embed
   * @param string $content
   * @return boolean
   */
  public function storeAdvancedVariables(Embed $embed, $content)
  {
    $dest = $this->getAdvancedVariablesPath($embed);
    
    return (File::put($dest, $content) !== false);
  }
  
  /**
   * Get content of advanced.
   * 
   * @param Embed $embed
   * @return NULL|string
   */
  public function retrieveAdvanced(Embed $embed)
  {
    $path = $this->getAdvancedPath($embed);
    return (File::exists($path) ? File::get($path) : null);
  }
  
  /**
   * Get content of advanced vars.
   * 
   * @param Embed $embed
   * @return NULL|string
   */
  public function retrieveAdvancedVariables(Embed $embed)
  {
    $path = $this->getAdvancedVariablesPath($embed);
    return (File::exists($path) ? File::get($path) : null);
  }
  
  /**
   * Render text for simple css.
   * 
   * @param Embed $embed
   * @return string
   */
  public function renderSimple(Embed $embed)
  {
    $simple_css = $embed->simple_css;
    $content = "";
    
    // body color
    $content .= "\$body-color: {$simple_css['color_body']};\n";
    
    // primary color
    $content .= "\$primary: {$simple_css['color_primary']};\n";
    
    // font-size-base
    $f_sz = round(floatval($simple_css['font_size'])/16, 2);
    $content .= "\$font-size-base: {$f_sz}rem;\n";
    
    // font-family
    $f_f = $simple_css['font_family'];
    if ($f_f == 'Nunito') {
      $content .= "@import url('https://fonts.googleapis.com/css?family=Nunito');\n";
    }
    $content .= "\$font-family-base: \"{$f_f}\", sans-serif;\n";
    
    return $content;
  }
 
  /**
   * Create the embed.scss file, depending on whether advanced or simple was saved.
   * 
   * @param Embed $embed
   * @param bool $advanced
   * @return boolean
   */
  public function createEmbedScss(Embed $embed, bool $advanced = false) 
  {
    $path = $this->getDestPath($embed) . '/embed.scss';
         
    $content = null;    

    // Variables
    $content .= "@import '../../../../resources/sass/_variables.scss';\n";
       
    // customized variables
    if ($advanced) {
      $content .= "@import 'advanced_variables';\n";
    } else {
      $content .= "@import 'simple';\n";
    }
    // Bootstrap
    $content .= "@import '~bootstrap/scss/bootstrap';\n";
    
    // app
    $content .= "@import '../../../../resources/sass/_app.scss';\n";
    
    // advanced text
    if ($advanced) {
      $content .= "@import 'advanced';\n";
    } 
    
    return (File::put($path, $content) !== false);
  }      
  
}