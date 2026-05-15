<?php

test('terms of use page is accessible to guests', function () {
    $this->get(route('legal.terms'))->assertOk();
});

test('privacy policy page is accessible to guests', function () {
    $this->get(route('legal.privacy'))->assertOk();
});

test('cookie policy page is accessible to guests', function () {
    $this->get(route('legal.cookies'))->assertOk();
});

test('data protection page is accessible to guests', function () {
    $this->get(route('legal.data-protection'))->assertOk();
});

test('transparency report page is accessible to guests', function () {
    $this->get(route('legal.transparency'))->assertOk();
});
