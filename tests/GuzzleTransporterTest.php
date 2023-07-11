<?php

use GuzzleHttp\Client;
use OpenAI\Laravel\GuzzleTransporter;

test('getRetryMiddleware returns a callable', function () {
    $instance = new GuzzleTransporter();

    $retryMiddleware = $instance->getRetryMiddleware();

    expect($retryMiddleware)->toBeCallable();
});

test('getClient returns a Client', function () {

    $instance = GuzzleTransporter::getClient();

    expect($instance)->toBeInstanceOf(Client::class);
});

test('getDelayDuration returns a callable', function () {
    $instance = new GuzzleTransporter();

    $delayCallable = $instance->getDelayDuration();

    expect($delayCallable)->toBeCallable();
});

test('getDecider returns a callable', function () {
    $instance = new GuzzleTransporter();

    $deciderCallable = $instance->getDecider();

    expect($deciderCallable)->toBeCallable();
});
