<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
  <div class="container-fluid">
    <a id="brand" class="navbar-brand" href="/">
      <img class="brand-logo" src="/img/logo_lg.png" alt="Logo" loading="lazy">
      <div class="brand-claim">{{ __('nav.brand_claim') }}</div>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse"
      data-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false"
      aria-label="{{ __('Toggle navigation') }}">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">{{ __('nav.home') }}</a></li>
        @auth
          <li class="nav-item"><a class="nav-link" href="{{ route('bike.index') }}">{{ __('nav.bike') }}</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('user.reservations') }}">{{ __('nav.user_reservations') }}</a></li>
          @php
          //should probably move this somewhere else though
          $self = Auth::user();
          $owned_bikes = $self->bikes;
          $editable_bikes = $self->editableBikes;
          $bikes = $owned_bikes->merge($editable_bikes);
          $chats = new Illuminate\Database\Eloquent\Collection();

          foreach($bikes as $b){
      			$chats = $chats->merge($b->chats);
    			}

    			$chats->load(['lastReads' => function ($query) use ($self) {
      			$query->where('user_id', '=', $self->id);
    			}, 'users', 'bikes']);

    			$chats = $chats->merge($self->chats);
    			$has_unread = false;
    			foreach($chats as $c){
    				$unread_query = $c->messages()->where('user_id', '<>', $self->id);
      			if($c->lastReads->count()){
        			$unread_query->where('created_at', '>=', $c->lastReads[0]->updated_at);
      			}
      			if($unread_query->count()){
        			$has_unread = true;
      				break;
      			}
    			}
          @endphp
          <li class="nav-item"><a class="nav-link" href="{{ route('chat.index') }}">
         	@if($has_unread)
          <span class="text-danger" title="{{__('chat.chats.unread')}}"><i class="fas fa-envelope-open-text fa-1x"></i></span>
          @else
          <span title="{{__('chat.chats.read')}}"><i class="fas fa-envelope fa-1x"></i></span>
          @endif
          {{ __('nav.chat') }}
          </a></li>
        @endauth
      </ul>
      <!-- Right Side Of Navbar -->
      <ul class="navbar-nav ml-auto">
        <!-- language select -->
        @include('navigation.language')
        <!-- Authentication Links -->
        @guest
          <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">{{ __('auth.login.login') }}</a></li>
          @if(Route::has('register'))
          <li class="nav-item"><a class="nav-link"href="{{ route('register') }}">{{ __('auth.register.register') }}</a></li>
          @endif
        @else
        <li class="nav-item dropdown">
          <a id="navbarDropdown"
            class="nav-link dropdown-toggle" href="#" role="button"
            data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false" v-pre> {{ Auth::user()->fullName }} <span
              class="caret"></span>
          </a>
          <div class="dropdown-menu dropdown-menu-right"
            aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="{{ route('user.edit')}}">{{__('nav.user_edit')}}</a>
            <a class="dropdown-item" href="{{ route('logout') }}"
            onclick="event.preventDefault();document.getElementById('logout-form').submit();">
            {{ __('auth.logout') }} </a>
            <form id="logout-form" action="{{ route('logout') }}"
              method="POST" style="display: none;">@csrf</form>
          </div>
        </li>
        @endguest
      </ul>
    </div>
  </div>
</nav>