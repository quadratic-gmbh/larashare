@component('mail::message')
{!!__('email.survey.text_top')!!}

@component('mail::button', ['url' => 'https://www.surveygizmo.eu/s3/90162991/KLIMAentLASTER-KurzfragebogenMB'])
{{__('email.survey.button')}}
@endcomponent

{!!__('email.survey.text_bottom')!!}

@component('mail::footer',['class' => 'text-right'])
{{__('email.footer')}}
@endcomponent
@endcomponent
