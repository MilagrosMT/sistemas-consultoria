
<?php
test('las respuestas incluyen las cabeceras de seguridad', function () {
    $response = $this->get('/');

    $response->assertOk();

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader(
        'Referrer-Policy',
        'strict-origin-when-cross-origin'
    );
    $response->assertHeader(
        'Permissions-Policy',
        'camera=(), microphone=(), geolocation=()'
    );
});
