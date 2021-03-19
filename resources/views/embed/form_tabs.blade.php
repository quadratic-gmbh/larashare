<?php  
  $disabled_btns = [];  
  if($embed == null) {
    $disabled_btns['edit'] = true;
    $disabled_btns['edit_bikes'] = true;
    $disabled_btns['show'] = true;
  } 
  
  $url_params = [];
  if ($embed !== null) {
    $url_params['id'] = $embed->id;
  }
  $fields = [
    [
      'name' => 'edit',
      'txt' => __('embed.form.title'),
      'url' => 'embed.edit'
    ],
    [
      'name' => 'edit_bikes',
      'txt' => __('embed.edit_bikes.title'),
      'url' => 'embed.edit_bikes'
    ],
    [
      'name' => 'show',
      'txt' => __('embed.show.title'),
      'url' => 'embed.show'
    ],
  ];
  $header = __('embed.form.tabs_header_' . ($embed === null ? 'new' : 'edit'))
?>
@component('components.tabs',[
  'header' => $header,
  'fields' => $fields,
  'url_params' => $url_params,
  'disabled_btns' => $disabled_btns,
  'embed' => $embed,
  'active' => $active
])
@endcomponent
