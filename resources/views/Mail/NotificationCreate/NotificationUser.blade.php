@component('mail::message')
# ¡Buen día {{$name}} {{$lastname}}!

<h2> Bienvenido al sistema... </h2>
<h2> Te han registrado con los siguientes datos. </h2>

<h3> Correo: {{$email}} </h3>
<h3> Puesto de trabajo: {{$job}} </h3>
<h3> Teléfono: {{$phone}} </h3>
<br>
Debes verificar tu cuenta, solo dale clic al siguiente botón y tu cuenta quedará verificada.
@component('mail::button', ['url' => route('verificar', ['token' => $remember_token])])
Verificar Cuenta
@endcomponent

@endcomponent