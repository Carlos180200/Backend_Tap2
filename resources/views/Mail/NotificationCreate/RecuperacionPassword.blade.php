@component('mail::message')

# ¡Buen día {{$name}} {{$lastname}}!

<h2> Comenzarás el proceso de recuperación de contraseña. </h2>

@component('mail::button', ['url' => 'https://www.youtube.com'])
Cambiar contraseña
@endcomponent

@endcomponent