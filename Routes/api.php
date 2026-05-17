<?php
use Illuminate\Http\Request;
use Orion\Facades\Orion;

Route::group([
    'as' => 'api.virkdata.',
    'prefix' => 'virkdata'
], function ($router) {
   
    
    Route::middleware(['auth.both:api'])->group(function () {

        
        Route::match(['get', 'post'], 'search/{query}', 'Api\VirkdataSearchController@search');


    });

});
