<?php

namespace App\Jobs;

use App\Embed;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Process\Process;
use Exception;
use App\Services\EmbedStyleProcessor;

/**
 * @deprecated
 */
class ProcessEmbedCss implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $embed;
    protected $advanced = false;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Embed $embed, bool $advanced = false)
    {
        $this->embed = $embed;
        $this->advanced = $advanced;
        $this->onQueue('embeds');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EmbedStyleProcessor $embed_style_processor)
    {       
      // create the embed scss
      $embed_style_processor->createEmbedScss($this->embed, $this->advanced);
      
       // execute the the node script to compile 
       $process = new Process('npm run embed -- --embed-id=' . $this->embed->id);
       $process->run();
       
       if (!$process->isSuccessful()) {
         Log::error("ProcesEmbedCss failed: executing npm script failed for ID" . $this->embed->id);
       }
       
       $this->embed->has_custom_css = true;
       $this->embed->save();       
    }    
}
