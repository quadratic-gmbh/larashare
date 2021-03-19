<?php  
  $disabled_btns = [];  
  if($bike == null) {
    $disabled_btns['edit'] = true;
    $disabled_btns['images'] = true;
    $disabled_btns['rental_period'] = true;
    $disabled_btns['review'] = true;
    $disabled_btns['publish'] = true;
  } else if($bike->rental_periods_count === 0) {
    $disabled_btns['review'] = true;
    $disabled_btns['publish'] = true;   
  } else if($bike->rental_places_count === 0) {
    $disabled_btns['rental_period'] = true;
    $disabled_btns['review'] = true;
    $disabled_btns['publish'] = true;
  }
?>
<h1 class="text-primary">
{{__('bike.form_tabs_header' . ($bike == null ? '_new' : ''))}}
</h1>
<p>{{__('general.required_hint')}}</p>
<div class="mb-3">
  <?php
  $fields = [
    [
      'name' => 'edit',
      'txt' => __('bike.form_tabs.edit'),
      'url' => 'bike.edit'
    ],
    [
      'name' => 'images',
      'txt' => __('bike.form_tabs.images'),
      'url' => 'bike.images'
    ],
    [
      'name' => 'rental_period',
      'txt' => __('bike.form_tabs.rental_period'),
      'url' => 'bike.rental_period'
    ],
    [
      'name' => 'review',
      'txt' => __('bike.form_tabs.review'),
      'url' => 'bike.rental_period_review'
    ],
    [
      'name' => 'publish',
      'txt' => __('bike.form_tabs.publish'),
      'url' => 'bike.publish'
    ]
  ];
  $current_field = null;
  $count_fields = count($fields);
  ?>
  <nav class="nav nav-pills">
  @for($i = 0; $i < $count_fields; $i++)
    <?php 
      $field = $fields[$i];
      $name = $field['name'];
      $active_txt = null;
      if ($active === $name) {
        $active_txt = 'active';
        $current_field = $i;
      }
      
      $disabled = null;
      $href = '#';
      if (($disabled_btns[$name] ?? false) || ($active === $name)) {            
        $disabled = 'disabled';    
      } else {
        $href = route($field['url'], ['bike_id' => $bike->id]);
      }
    ?>
    <li class="nav-item">
      <a class="nav-link {{$active_txt . ' ' . $disabled}}" href="{{$href}}">
      {{($i + 1) . '. ' . $field['txt']}}
      </a>
    </li>
  @endfor
  </nav>
  <?php 
  $href_prev = null;
  $href_next = null;
  if ($current_field > 0) {
    for ($i = $current_field - 1; $i >= 0; $i--) {
      $field = $fields[$i];
      $name = $field['name'];
      if ($disabled_btns[$name] ?? false) {
        continue; 
      } else {
        $href_prev = route($field['url'], ['bike_id' => $bike->id]);
        break;
      }
    }  
  }
  
  if ($current_field < $count_fields - 1) {
    for ($i = $current_field + 1; $i < $count_fields; $i++) {
      $field = $fields[$i];
      $name = $field['name'];
      if ($disabled_btns[$name] ?? false) {
        continue;
      } else {
        $href_next = route($field['url'], ['bike_id' => $bike->id]);
        break;
      }
    }
  }
  ?>
  <div class="mt-1">
  @if($href_prev)
    <a href="{{$href_prev}}" class="btn btn-secondary">{{__('general.back')}}</a>
  @endif
  @if($href_next)
    <a href="{{$href_next}}" class="btn btn-secondary">{{__('general.next')}}</a>
  @endif 
  </div>
</div>