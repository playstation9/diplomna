<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'food';
    
    
    public function saveNewEntry($data)
    {
                
        $this->title = $data->title;
        $this->units = $data->units;
        $this->calories_per_unit = $data->calories;
        $this->save();
        
        return redirect('/food');                
        
    }
    
    public function updateEntry($entry, $data)
    {
        
        $entry->title = $data['title'];
        $entry->units = $data['units'];
        $entry->calories_per_unit = $data['calories'];
        $entry->save();
        
        return redirect('/food');      
    }
}
