<?php

use App\Livewire\Landing\DemoRequestForm;
use App\Models\DemoRequest;
use Livewire\Livewire;

it('renders the landing page', function () {
    $this->get('/')->assertOk()->assertSee('PrComet');
});

it('captures a valid demo request', function () {
    Livewire::test(DemoRequestForm::class)
        ->set('name', 'Lee Tengum')
        ->set('email', 'lee@example.com')
        ->set('company', 'Aurelian Mining')
        ->set('role', 'Head of Comms')
        ->set('website', 'https://aurelian.example')
        ->set('notes', 'We have a busy Q3 of drill results coming up.')
        ->call('submit')
        ->assertSet('submitted', true);

    $request = DemoRequest::first();
    expect($request)->not->toBeNull()
        ->and($request->name)->toBe('Lee Tengum')
        ->and($request->email)->toBe('lee@example.com')
        ->and($request->company)->toBe('Aurelian Mining')
        ->and($request->status)->toBe(DemoRequest::STATUS_NEW);
});

it('requires name, email, and company', function () {
    Livewire::test(DemoRequestForm::class)
        ->set('name', '')
        ->set('email', '')
        ->set('company', '')
        ->call('submit')
        ->assertHasErrors(['name' => 'required', 'email' => 'required', 'company' => 'required']);

    expect(DemoRequest::count())->toBe(0);
});

it('validates the email format', function () {
    Livewire::test(DemoRequestForm::class)
        ->set('name', 'Lee')
        ->set('email', 'not-an-email')
        ->set('company', 'Co')
        ->call('submit')
        ->assertHasErrors(['email' => 'email']);
});

it('validates website as URL when provided', function () {
    Livewire::test(DemoRequestForm::class)
        ->set('name', 'Lee')
        ->set('email', 'lee@example.com')
        ->set('company', 'Co')
        ->set('website', 'just-a-string')
        ->call('submit')
        ->assertHasErrors(['website' => 'url']);
});
