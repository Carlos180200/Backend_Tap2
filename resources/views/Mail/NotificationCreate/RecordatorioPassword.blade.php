@component('mail::message')

# ¡Buen día {{$name}} {{$lastname}}!

<h2> Tu información de inicio de sesión es: </h2>
<h3> Email: {{$email}}</h3>
<h3> Password: {{$password}}</h3>

<h3>Nota: Una vez ingresando al sistema tendrás la oportunidad de modificar tu contraseña.</h3>

@endcomponent