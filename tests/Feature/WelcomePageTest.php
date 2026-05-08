<?php

test('welcome page loads successfully', function () {
    $this->get(route('home'))->assertOk();
});

test('welcome page has navbar with university name', function () {
    $this->get(route('home'))
        ->assertSeeText('BICOL UNIVERSITY');
});

test('welcome page has hero headline', function () {
    $this->get(route('home'))
        ->assertSee('Get the help')
        ->assertSee('you need');
});

test('welcome page has new request and track columns', function () {
    $response = $this->get(route('home'));
    $response->assertSeeText('New Request');
    $response->assertSeeText('Track a Ticket');
    $response->assertSeeText('Submit Now');
    $response->assertSeeText('Track Now');
});

test('welcome page has how it works section', function () {
    $this->get(route('home'))
        ->assertSeeText('How It Works');
});

test('welcome page has departments section', function () {
    $response = $this->get(route('home'));
    $response->assertSeeText('Departments');
    $response->assertSeeText('Information Technology Office');
    $response->assertSeeText('Physical Plant Office');
});

test('welcome page has footer with technical support', function () {
    $response = $this->get(route('home'));
    $response->assertSeeText('Technical Support');
    $response->assertSee('itsupport@bicol-u.edu.ph');
});
