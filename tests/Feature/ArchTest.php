<?php

uses()->group('Architecture');

// --- Preset rules ---
arch()->preset()->php();
arch()->preset()->laravel();

// --- Debug & global functions ---
arch('no debug or global functions')
    ->expect(['dd', 'dump', 'ray', 'exit', 'die', 'global_function_name'])
    ->not->toBeUsed();

// --- Facade usage restrictions ---
$facades = [
    \Illuminate\Support\Facades\Validator::class,
    \Illuminate\Support\Facades\App::class,
    \Illuminate\Support\Facades\Cache::class,
    \Illuminate\Support\Facades\Config::class,
    \Illuminate\Support\Facades\Event::class,
    \Illuminate\Support\Facades\Log::class,
    \Illuminate\Support\Facades\Request::class,
    \Illuminate\Support\Facades\Route::class,
    \Illuminate\Support\Facades\URL::class,
    \Illuminate\Support\Facades\Lang::class,
    \Illuminate\Support\Facades\View::class,
];

arch('no forbidden facades')
    ->expect($facades)
    ->not->toBeUsed();

// --- Layered architecture rules ---
$layerRules = [
    [
        'name' => 'models',
        'namespace' => 'App\Models',
        'extends' => \Illuminate\Database\Eloquent\Model::class,
        'traits' => [\Illuminate\Database\Eloquent\Factories\HasFactory::class],
        'suffix' => null,
        'forbidden' => [
            'App\Http\Controllers',
            'App\Http\Requests',
        ],
    ],
    [
        'name' => 'controllers',
        'namespace' => 'App\Http\Controllers',
        'extends' => \App\Http\Controllers\Controller::class,
        'suffix' => 'Controller',
        'forbidden' => [
            \Illuminate\Support\Facades\DB::class,
            'App\Models',
            \Illuminate\Database\Eloquent\Model::class,
            \Illuminate\Support\Facades\Cache::class,
            \Illuminate\Support\Facades\Route::class,
            \Illuminate\Support\Facades\Storage::class,
        ],
    ],
    [
        'name' => 'requests',
        'namespace' => 'App\Http\Requests',
        'extends' => \Illuminate\Foundation\Http\FormRequest::class,
        'suffix' => 'Request',
    ],
    [
        'name' => 'middleware',
        'namespace' => 'App\Http\Middleware',
        'suffix' => 'Middleware',
    ],
    [
        'name' => 'commands',
        'namespace' => 'App\Console\Commands',
        'extends' => \Illuminate\Console\Command::class,
        'suffix' => 'Command',
    ],
    [
        'name' => 'exceptions',
        'namespace' => 'App\Exceptions',
        'extends' => \Exception::class,
        'suffix' => 'Exception',
    ],
    [
        'name' => 'services',
        'namespace' => 'App\Services',
        'forbidden' => [
            \Illuminate\Http\Request::class,
            \Illuminate\Support\Facades\Session::class,
            \Illuminate\Database\Eloquent\Model::class,
            \Illuminate\Support\Facades\DB::class,
            \Illuminate\Support\Facades\Cache::class,
            \Illuminate\Support\Facades\Route::class,
            \Illuminate\Support\Facades\Storage::class,
        ],
    ],
    [
        'name' => 'livewire',
        'namespace' => 'App\Http\Livewire',
        'forbidden' => [
            \Illuminate\Database\Eloquent\Model::class,
            \Illuminate\Support\Facades\DB::class,
            \Illuminate\Support\Facades\Cache::class,
            \Illuminate\Support\Facades\Route::class,
            \Illuminate\Support\Facades\Storage::class,
        ],
    ],
];

// --- Apply layered rules ---
foreach ($layerRules as $rule) {
    $arch = arch($rule['name'])->expect($rule['namespace']);

    if (isset($rule['extends'])) {
        $arch->toExtend($rule['extends']);
    }
    if (isset($rule['traits'])) {
        foreach ($rule['traits'] as $trait) {
            $arch->toUseTraits($trait);
        }
    }
    if (isset($rule['suffix'])) {
        $arch->toHaveSuffix($rule['suffix']);
    }
    $arch->toBeClasses();

    if (isset($rule['forbidden'])) {
        foreach ((array)$rule['forbidden'] as $forbidden) {
            $arch->not->toUse($forbidden);
        }
    }
}

// --- PowerGrid tables ---
arch('powergrid tables')
    ->expect('App\Http\Livewire\Tables')
    ->toHaveSuffix('Table')
    ->toExtend(\PowerComponents\LivewirePowerGrid\PowerGridComponent::class)
    ->toBeClasses()
    ->toHaveMethod('dataSource')
    ->toHaveMethod('columns')
    ->toHaveMethod('filters')
    ->toHaveMethod('actions');
