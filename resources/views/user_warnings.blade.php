@isset($user_warnings)
<div class="container py-3">
  @foreach($user_warnings as $warning) 
  <div class="alert alert-warning">
    <i class="fas fa-exclamation-circle"></i>
    <span class="mx-3">{{$warning['text']}}</span>
    <a class="btn btn-sm btn-warning" href="{{$warning['url']}}"><i class="fas fa-wrench"></i></a>  
  </div>
  @endforeach
</div>
@endisset
