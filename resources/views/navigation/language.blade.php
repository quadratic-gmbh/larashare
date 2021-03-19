<li class="nav-item dropdown">
  <a id="lang-dropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
   {{__('nav.language')}}
  </a>
  <div class="dropdown-menu dropdown-menu-right" aria-labelledby="lang-dropdown">
  @php
  $locale = App::getLocale();
  $locales = explode(',',config('app.public_locales'));
  foreach($locales as $l) :
  @endphp
    <a class="dropdown-item {{$locale === $l ? 'active' : ''}}" href="{{ route('select_locale', ['locale' => $l]) }}">
    {{__('languages.' . $l)}}
    </a>
  @php
  endforeach;
  @endphp
  </div>
</li>
