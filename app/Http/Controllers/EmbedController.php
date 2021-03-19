<?php

namespace App\Http\Controllers;

use App\Bike;
use App\BoxType;
use App\Embed;
use App\Jobs\ProcessEmbedCss;
use App\Rules\ColorRule;
use App\Services\EmbedStyleProcessor;
use App\Services\InputFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmbedController extends Controller
{
  /**
   * Allowed fonts used for simple embed styling.
   * 
   * @deprecated
   * 
   * @var array
   */
  protected $fonts = [
    'Arial',
    'Georgia',
    'Nunito',
    'Lucida Bright',
    'Tahoma',
    'Times New Roman',
    'Verdana'
  ];
  
  /**
   * Shows embeds.
   * 
   * @param Request $request
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function index(Request $request)
  {    
    $embeds = $request->user()->embeds;
    
    return view('embed.index',[
      'embeds' => $embeds
    ]);
  }
  
  /**
   * Create embed.
   * 
   * @param Request $request
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function create(Request $request)
  {
    return $this->editOrCreate($request);    
  }
  
  /**
   * Edit embed.
   * 
   * @param Request $request
   * @param int $id
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function edit(Request $request, int $id)
  {
    $embed = Embed::findOrFail($id);
    $this->authorize('modify',$embed);
    
    return $this->editOrCreate($request, $embed);
  }
  
  /**
   * Edit or create embed.
   * 
   * @param Request $request
   * @param Embed $embed
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  private function editOrCreate(Request $request, Embed $embed = null)
  {
    $edit_mode = ($embed !== null);
    $form_data = [];    
    if ($edit_mode) {      
      $form_data['name'] = $embed->name;
      $defaults = $embed->defaults;
      $form_data['search.location'] = data_get($defaults, 'search.location');
    }
    
    $view_data = [
      'edit_mode' => $edit_mode,
      'form_data' => $form_data,
      'embed' => $embed
    ];
    return view('embed.form',$view_data);
  }
 
  /**
   * Store embed.
   * 
   * @param Request $request
   * @param InputFilter $if
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(Request $request, InputFilter $if)
  {    
    return $this->updateOrStore($request);
  }  
  
  /**
   * Update embed.
   * 
   * @param Request $request
   * @param int $id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(Request $request,   int $id)
  {
    $embed = Embed::findOrFail($id);
    $this->authorize('modify',$embed);
    return $this->updateOrStore($request, $embed);
  }
  
  /**
   * Update or store embed.
   * 
   * @param Request $request
   * @param Embed $embed
   * @return \Illuminate\Http\RedirectResponse|unknown
   */
  private function updateOrStore(Request $request, Embed $embed = null)
  {
    $edit_mode = ($embed !== null);
    
    $input = $request->except('_token');
    
    $validator = Validator::make($input,[
      'name' => ['required','string','max:255'],
      'search.location' => ['nullable','string','max:255']
    ]);
    
    if ($validator->fails()) {
      return back()
      ->withInput($input)
      ->withErrors($validator);
    }
    
    // flatten inputs for filtering    
    $input = $this->filterUpdateInput($input);   
    $defaults = $this->extractUpdateDefaults(Arr::except($input,['name']));    
    
    if (!$edit_mode) {
      $user = $request->user();
      $embed = Embed::create([
        'name' => $input['name'],
        'defaults' => $defaults,
        'user_id' => $user->id
      ]);
    } else {
      $embed->name = $input['name'];
      $embed->defaults = $defaults;
      $embed->save();
    }          
    
    return redirect()->route('embed.edit_bikes',['id' => $embed->id]);
  }
  
  /**
   * Filter update input.
   * 
   * @param unknown $input
   * @return array|iterable[]
   */
  private function filterUpdateInput($input) 
  {
    $input_dot = Arr::dot($input);
    $if = resolve('App\Services\InputFilter');
    $if_rules = [
      'required' => [
        'name' => InputFilter::TYPE_STR
      ],
      'nullable' => [
        'search.location' => InputFilter::TYPE_STR
      ]
    ];
    $if->filter($if_rules, $input_dot);
    
    return $input_dot;
  }
  
  /**
   * Extract update defaults.
   * 
   * @param unknown $input
   * @return array|NULL
   */
  private function extractUpdateDefaults($input) 
  {
    $defaults = [];
       
    $input_non_null = Arr::where($input, function($value, $key) {
      return $value !== null;
    });        
      
    foreach($input_non_null as $key => $value) {
      data_set($defaults, $key, $value); 
    }
    if(empty($defaults)) {
      $defaults = null;
    }
    
    return $defaults;
  }
  
  /**
   * Edit bikes.
   * 
   * @param Request $request
   * @param int $id
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function editBikes(Request $request, int $id)
  {
    $user = $request->user();
    $embed = Embed::with('bikes')->findOrFail($id);
    $this->authorize('modify',$embed);
    
    $bikes = Bike::public()->with('rentalPlaces')->get();
    
    $grouped_bikes = $bikes->mapToGroups(function ($item, $key) use ($user) {
      if ($item->user_id == $user->id) {
        return ['own' => $item];
      }
      return ['other' => $item];
    });
    
    $sorted_bikes = [];
    foreach (['own', 'other'] as $field){
      if ($grouped_bikes->has($field)) {
        $sorted_bikes[] = $grouped_bikes[$field];
      }
    }
    
    $selected_bikes = $embed->bikes->mapWithKeys(function($item) {
      return ['bike.' . $item->id => true];
    });
    
    $view_data = [
      'embed' => $embed,
      'sorted_bikes' => $sorted_bikes,      
      'selected_bikes' => $selected_bikes
    ];
    return view('embed.edit_bikes',$view_data);
  }
 
  /**
   * Edit styling.
   * 
   * @deprecated
   * 
   * @param Request $request
   * @param EmbedStyleProcessor $embed_style_processor
   * @param int $id
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function editStyling(Request $request,EmbedStyleProcessor $embed_style_processor, int $id)
  {
    $user = $request->user();
    $embed = Embed::findOrFail($id);
    $this->authorize('modify',$embed);
    
    $form_data = [];
    
    $advanced_example = null;
    if ($embed->simple_css !== null) {
      foreach($embed->simple_css as $field=> $value) {
        $form_data[$field] = $value;
      }     
      $advanced_example = $embed_style_processor->renderSimple($embed);
    } else {
      $form_data['font_size'] = 16;
    }
    
    $allowed_fonts = collect($this->fonts)->transform(function($item, $key) {
      return ['value' => $item, 'text' => $item, 'style' => "font-family: '{$item}'"];
    });           
    
    $advanced_variables = $embed_style_processor->retrieveAdvancedVariables($embed);
    $advanced_text = $embed_style_processor->retrieveAdvanced($embed);
    
    $view_data = [
      'embed' => $embed,
      'form_data' => $form_data,
      'advanced_example' => $advanced_example,
      'allowed_fonts' => $allowed_fonts,
      'advanced_text' => $advanced_text,
      'advanced_variables' => $advanced_variables
    ];
    
    return view('embed.edit_styling', $view_data);
  }
  
  /**
   * Update simple styling.
   * 
   * @deprecated
   * 
   * @param Request $request
   * @param EmbedStyleProcessor $embed_style_processor
   * @param int $id
   * @return \Illuminate\Http\RedirectResponse|unknown
   */
  public function updateStylingSimple(Request $request, EmbedStyleProcessor $embed_style_processor, int $id)
  {
    $user = $request->user();
    $embed = Embed::findOrFail($id);
    $this->authorize('modify',$embed);
    
    $input = $request->except('_token');
    
    $rules = [    
      'color_body' => ['required',new ColorRule()],
      'color_primary' => ['required',new ColorRule()],
      'font_size' => ['required','numeric','min:0'],
      'font_family' => ['required',function($atttribute, $value, $fail) {
        if (!in_array($value, $this->fonts)) {
          $fail(__('validation.custom.allowed_font_family'));
        }
      }],
    ];
    $validator = Validator::make($input,$rules);  
     
    if ($validator->fails()) {
      return back()
      ->withInput($input)
      ->withErrors($validator);
    }
    $simple_css = $embed->simple_css;
    if($simple_css === null) {
      $simple_css = [];
    }
        
    $simple_css = $input;
    
    $embed->simple_css = $simple_css;
    
    $dirty = $embed->isDirty();
    $embed->save();
    // only process css if it actually changed
    if ($dirty) {
      // update simple.scss
      if (!$embed_style_processor->storeSimple($embed)) {
        Log::error('EmbedController::updateStylingSimple: failed to save simple.css for embed:' . $id);
        return back()
        ->withErrors(['simple_failed' => __('embed.edit_styling.simple_error_failed')])
        ->withInput($input);
      }
      
      ProcessEmbedCss::dispatch($embed, false);
    }
    $request->session()->flash('update_success',true);
    return redirect()->route('embed.edit_styling',['id' => $id]);
  }
  
  /**
   * Update advanced styling.
   * 
   * @deprecated
   * 
   * @param Request $request
   * @param EmbedStyleProcessor $embed_style_processor
   * @param int $id
   * @return \Illuminate\Http\RedirectResponse|unknown
   */
  public function updateStylingAdvanced(Request $request, EmbedStyleProcessor $embed_style_processor, int $id)
  {
    $user = $request->user();
    $embed = Embed::findOrFail($id);
    $this->authorize('modify',$embed);
    
    $input = $request->except('_token');
    // TODO: validate css?
    // TODO: filter css?
    
    // get old md5 hash of files    
    $old_hash = $embed_style_processor->getAdvancedHash($embed);
    $old_var_hash = $embed_style_processor->getAdvancedVariablesHash($embed);
    
    // try to store variables
    if (!$embed_style_processor->storeAdvancedVariables($embed, $input['variables'])) {
      Log::error('EmbedController::updateStylingAdvanced: failed to save advanced variables css for embed:' . $id);
      $request->session()->flash('show_advanced',true);
      return back()
      ->withErrors(['variables' => __('embed.edit_styling.advanced_error_failed')])
      ->withInput($input);
    }
    
    // try to store text
    if (!$embed_style_processor->storeAdvanced($embed, $input['text'])) {
      Log::error('EmbedController::updateStylingAdvanced: failed to save advanced css for embed:' . $id);
      $request->session()->flash('show_advanced',true);
      return back()
      ->withErrors(['text' => __('embed.edit_styling.advanced_error_failed')])
      ->withInput($input);
    }
    
    // get new hashes
    $new_hash = $embed_style_processor->getAdvancedHash($embed);
    $new_var_hash = $embed_style_processor->getAdvancedVariablesHash($embed);
    
    // dispatch the process job if files got modified
    if (strcmp($old_hash, $new_hash) || strcmp($old_var_hash, $new_var_hash)) {
      ProcessEmbedCss::dispatch($embed, true);
    }
    
    $request->session()->flash('update_success',true);
    $request->session()->flash('show_advanced',true);
    return redirect()->route('embed.edit_styling',['id' => $id]);
  }
  
  /**
   * Update bikes.
   * 
   * @param Request $request
   * @param int $id
   * @return \Illuminate\Http\RedirectResponse|unknown
   */
  public function updateBikes(Request $request, int $id)
  {
    $user = $request->user();
    $embed = Embed::with('bikes')->findOrFail($id);
    $this->authorize('modify',$embed);
    
    $input = $request->input();    
         
    $validator = Validator::make($input,[  
      'bike.*' => [
        'required',
        'integer',
        function ($attribute, $value, $fail) {
          $id = str_replace('bike.', '', $attribute);
          $exists = DB::table('bikes')->where([
            'id' => intval($id),
            'deleted_at' => null,
            'public' => 1
          ])->exists();
          if(!$exists) {
            $fail(__('validation.custom.embed_edit_bikes_doesnt_exist'));
          }
        }
      ]
    ]);    
    
    if ($validator->fails()) {
      return back()
      ->withInput($input)
      ->withErrors($validator);
    }
          
    $bike_ids = array_keys($input['bike'] ?? []);    
    $embed->bikes()->sync($bike_ids);
    
    $request->session()->flash('update_success',true);
    return redirect()->route('embed.edit_bikes',['id' => $id]);
  }
  
  /**
   * Allow all bikes.
   * 
   * @param Request $request
   * @param int $id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function updateBikesAllowAll(Request $request, int $id)
  {   
    $embed = Embed::with('bikes')->findOrFail($id);
    $this->authorize('modify',$embed);
    
    if(!$request->has('allow_all')) {
      abort(500);
    }
    
    // detach all bikes to quickly allow all for search etc
    $embed->bikes()->detach();    
    
    $request->session()->flash('update_success',true);
    return redirect()->route('embed.edit_bikes',['id' => $id]);
  }
  
  /**
   * Show embed.
   * 
   * @param Request $request
   * @param int $id
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function show(Request $request, int $id)
  {   
    $embed = Embed::findOrFail($id);
    $this->authorize('modify',$embed);
    
    return view('embed.show',['embed' => $embed]);
  }
  
  /**
   * Confirm embed deletion.
   * 
   * @param Request $request
   * @param int $id
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function destroyAsk(Request $request, int $id)
  {    
    $embed = Embed::findOrFail($id);
    $this->authorize('modify',$embed);
    
    return view('general.destroy_ask', [
      'header' => __('embed.destroy_ask.header', ['name' => $embed->name]),
      'route' => route('embed.destroy', ['id' => $embed]),
      'route_back' => route('embed.index')
    ]);
  }
  
  /**
   * Show delete embed.
   * 
   * @param Request $request
   * @param int $id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Request $request, int $id)
  {
    $embed = Embed::findOrFail($id);
    $this->authorize('modify',$embed);
    
    $embed->bikes()->detach();
    $embed->delete();
    
    return redirect()->route('embed.index');
  }  
  
  /**
   * Get client config.
   * 
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function apiClientConfig(Request $request) 
  {
    $embed_id = intval($request->get('embed_id'));
    $embed = Embed::find($embed_id);
    
    // wrong embed id or embed doesnt exist - return default config
//     if(!$embed) {
//       return response()->json($this->apiClientConfigDefaults());
//     }    
    // TODO: actual configuration ?
    $data = $this->apiClientConfigDefaults();
    if ($embed) {
      $defaults = $embed->defaults;
      data_set($data, 'search.defaults.location', data_get($defaults,'search.location','Wien'));
    }
    
    return response()->json($data);    
  }    
  
  private function apiClientConfigDefaults() {
    return [
      'box_type_ids' => BoxType::getIdMapping(),      
      'search' => [
        'defaults' => [
          'location' => 'Wien',
          'date' => now()->format('Y-m-d'),
          'duration' => 2,
          'duration_type' => 'h'
        ]
      ]
    ];
  }
}
