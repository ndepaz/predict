<?php
/**
 * Created by Noe
 * Date: 2/27/2019
 * Time: 9:33 AM
 */
namespace Ndp\Predict;

use Illuminate\Support\ServiceProvider;

class PredictTimeServiceProvider extends ServiceProvider
{
    public function boot(){
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

    }
    public function register(){

    }
}