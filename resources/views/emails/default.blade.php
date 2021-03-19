@component('mail::message')
{{$text}}
    
@isset($url)
@component('mail::button', ['url' => $url])
{{$button}}
@endcomponent
@endisset
    
@endcomponent
