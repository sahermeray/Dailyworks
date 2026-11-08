<?php

test('guests are redirected to the login page from the application root', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
