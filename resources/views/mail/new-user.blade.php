@component('mail::message')
Merhaba {{ $user->name }},
<p> Hesabınız başarıyla oluşturuldu. İşte geçici şifreniz ve e-posta adresiniz:</p>
<p><strong>Eposta: {{ $user->email}}</strong></p>
<p><strong>Parola: {{ $password}}</strong></p>
<p>
    Lütfen bu şifre ile giriş yapın ve mümkün olan en kısa sürede değiştirin</p>
<br>

Teşekkürler,<br>

{{ config('app.name') }}
@endcomponent