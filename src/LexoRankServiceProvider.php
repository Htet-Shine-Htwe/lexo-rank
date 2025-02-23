<?php

namespace Dede\Lexorank;

class LexoRankServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/lexorank.php' => config_path('lexorank.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/lexorank.php', 'lexorank');
    }
}





